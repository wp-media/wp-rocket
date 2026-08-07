<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Activation\Activation;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\MCP\OAuth\Bootstrap;

/**
 * Regression test for #8558, rewritten for the wp-media/mcp-oauth lazy-flush model.
 *
 * #8558's guarantee: the MCP OAuth endpoints must resolve on the FIRST front-end
 * request, with no manual "save permalinks" step.
 *
 * Old model (removed): \WP_Rocket\Engine\Activation\Activation::activate_plugin()
 * registered the OAuth + .well-known rewrite rules on the 'rocket_activation' hook
 * and flushed once, eagerly, during activation.
 *
 * New model (this test): the OAuth flow lives in the wp-media/mcp-oauth library.
 * The library registers its rewrite rules on 'init' (priority 10) and
 * \WPMedia\MCP\OAuth\Bootstrap::maybe_flush_rewrite_rules() (init priority 20)
 * flushes them lazily — once per REWRITE_VERSION bump, keyed off the
 * 'wpmedia_mcp_oauth_rewrite_version' option. There is no activation-hook flush
 * anymore. Because the flush is driven by 'init' (which runs on every request)
 * and gated by a version option (not by the activation hook), the rules
 * materialize on:
 *   - the first request after a FRESH activation (version flag absent), and
 *   - the first request after a SILENT plugin update (activation hook never
 *     fires on update, so only the init version-flag flush can cover it).
 *
 * WP Rocket does not cache 404 responses, so there is no stale-404 masking to
 * work around: as soon as the rules are flushed, the endpoints resolve.
 *
 * @group Activation
 * @group MCP
 */
class ActivatePluginTest extends TestCase {

	/**
	 * Rewrite-version option the library uses as its lazy-flush flag.
	 */
	private const REWRITE_OPTION = 'wpmedia_mcp_oauth_rewrite_version';

	/**
	 * OAuth + .well-known rewrite rule keys the library must register.
	 *
	 * @var string[]
	 */
	private $expected_rules = [
		'^oauth/authorize$',
		'^oauth/token$',
		'^\\.well-known/oauth-protected-resource$',
		'^\\.well-known/oauth-authorization-server$',
	];

	/**
	 * Administrator used to create additional sites in the multisite test.
	 *
	 * @var int
	 */
	private static $user_id;

	/**
	 * Whether the multisite test switched to another blog.
	 *
	 * @var bool
	 */
	private $switched_blog = false;

	/**
	 * Create the administrator used for multisite blog creation.
	 *
	 * @param \WP_UnitTest_Factory $factory Test factory.
	 * @return void
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	/**
	 * Install the tables the permalink_structure_changed hook touches, so
	 * flushing rewrite rules does not hit missing-table DB errors.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		if (
			! class_exists( \WP\MCP\Core\McpAdapter::class )
			|| ! version_compare( $GLOBALS['wp_version'] ?? '0', '6.9', '>=' )
			|| ! function_exists( 'wp_register_ability' )
			|| ! function_exists( 'wp_get_ability' )
			|| ! function_exists( 'wp_get_abilities' )
			|| ! function_exists( 'wp_register_ability_category' )
		) {
			$this->markTestSkipped( 'MCP OAuth boots only with the MCP adapter + WP 6.9 Abilities API (see inc/main.php $rocket_can_boot_mcp_adapter).' );
		}

		self::installPreloadCacheTable();
		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();

		// The silent-update path can reach RocketInsights' wp_rocket_upgrade
		// handler, which queries the performance-monitoring table. Install it so
		// firing the upgrade does not emit a missing-table DB error (which would
		// mark the test risky under beStrictAboutOutputDuringTests).
		self::installPerformanceMonitoringTable();

		add_filter( 'wpmedia_mcp_oauth_server_enabled', '__return_true' );

		// The test fires do_action('init'), which triggers every BerlinDB table's
		// maybe_upgrade hook; for tables not installed above (rocket_cdn, rucss) that
		// re-creates their temporary table and emits a "table already exists" DB error.
		// Remove those hooks (this isolates hooks, it does not install any table); the
		// tables the flush actually queries are installed above.
		self::removeDBHooks();
	}

	/**
	 * Remove hooks and reset the lazy-flush flag set during the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		if ( $this->switched_blog ) {
			restore_current_blog();
			$this->switched_blog = false;
		}

		remove_filter( 'wpmedia_mcp_oauth_server_enabled', '__return_true' );

		// Reset the library's lazy-flush flag via its public API.
		Bootstrap::schedule_rewrite_flush();

		self::uninstallPreloadCacheTable();
		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down();
	}

	/**
	 * On the first request after a fresh activation (version flag absent), firing
	 * 'init' must register AND flush the OAuth + .well-known rewrite rules — no
	 * activation-hook flush required.
	 *
	 * @return void
	 */
	public function testShouldFlushOauthRewriteRulesOnFirstRequestAfterActivation() {
		$this->set_permalink_structure( '/%postname%/' );

		// Fresh activation: the library has never flushed for the current version.
		Bootstrap::schedule_rewrite_flush();

		// The first front-end request fires 'init'; the library registers its rules
		// (priority 10) and lazily flushes them (priority 20).
		do_action( 'init' );

		$this->assertOauthRulesArePersisted();
	}

	/**
	 * On the first request after a SILENT plugin update, the activation hook never
	 * fired, so only the library's init version-flag flush can materialize the
	 * rules. Simulate a stale version flag and assert the rules still flush.
	 *
	 * @return void
	 */
	public function testShouldFlushOauthRewriteRulesOnFirstRequestAfterSilentUpdate() {
		$this->set_permalink_structure( '/%postname%/' );

		// Silent update: a previous (stale) rewrite version is stored, and the
		// activation hook did not run. The init p20 flush must reconcile it.
		// This is the library's own version-flag option, not a WP Rocket setting,
		// so it is written directly rather than through Options_Data.
		update_option( self::REWRITE_OPTION, '0', false ); // @phpstan-ignore custom.rules.discourageOptionUsage

		do_action( 'init' );

		$this->assertOauthRulesArePersisted();
	}

	/**
	 * After a network activation, each subsite gets its own lazy flush: the
	 * 'wpmedia_mcp_oauth_rewrite_version' option is a regular (per-site) option,
	 * not a network option, so it is absent on a fresh subsite and the library's
	 * init flush materializes the OAuth rules on that subsite's first request.
	 *
	 * @group Multisite
	 *
	 * @return void
	 */
	public function testShouldFlushOauthRewriteRulesOnFirstRequestPerSubsiteAfterNetworkActivation() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'This test requires a multisite installation.' );
		}

		$blog_id = $this->factory()->blog->create( [ 'user_id' => self::$user_id ] );

		switch_to_blog( $blog_id );
		$this->switched_blog = true;

		// Ensure the subsite has the tables touched by permalink_structure_changed.
		self::installPreloadCacheTable();
		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();
		self::installPerformanceMonitoringTable();

		// A brand-new subsite has never flushed (the flag is a per-site option).
		$this->set_permalink_structure( '/%postname%/' );

		do_action( 'init' );

		$this->assertOauthRulesArePersisted();
	}

	/**
	 * Assert the OAuth + .well-known rewrite rules are present in the generated
	 * rewrite rules after the library's lazy flush.
	 *
	 * @return void
	 */
	private function assertOauthRulesArePersisted() {
		global $wp_rewrite;
		$rewrite_rules = $wp_rewrite->wp_rewrite_rules();

		$this->assertIsArray( $rewrite_rules );

		foreach ( $this->expected_rules as $rule ) {
			$this->assertArrayHasKey( $rule, $rewrite_rules );
		}
	}
}
