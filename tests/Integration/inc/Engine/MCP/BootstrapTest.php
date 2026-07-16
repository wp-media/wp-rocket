<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\MCP;

use WP_Rocket\Engine\MCP\Compat\DeprecatedFilters;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\MCP\OAuth\Auth\ClaudeClientVerifier;
use WPMedia\MCP\OAuth\Auth\SecretManager;
use WPMedia\MCP\OAuth\Bootstrap;

/**
 * Test class covering the boot of the wp-media/mcp-oauth library from inc/main.php.
 *
 * The OAuth flow now lives entirely in the library; WP Rocket only boots it via
 * \WPMedia\MCP\OAuth\Bootstrap::instance() (called from inc/main.php at plugin
 * load) and manages the two deprecated filters. This test asserts:
 *   - the library is wired to WordPress on boot (its 'init' hooks are registered);
 *   - the OAuth endpoints route when the server is enabled via the new filter;
 *   - they return a 404 (no rewrite rule) when the server is disabled;
 *   - the legacy 'rocket_mcp_oauth_server_enabled' filter still enables the
 *     server through the library's back-compat bridge;
 *   - the legacy 'rocket_mcp_trusted_publishers' filter *value* still reaches
 *     the CIMD trust check (ClaudeClientVerifier) through the library's
 *     back-compat bridge.
 *
 * @group MCP
 */
class BootstrapTest extends TestCase {

	/**
	 * Install the tables the permalink_structure_changed hook touches, so
	 * flushing rewrite rules does not hit missing-table DB errors.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		self::installPreloadCacheTable();
		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();
	}

	/**
	 * Clean up hooks and the library's lazy-flush flag set during the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'wpmedia_mcp_oauth_server_enabled', '__return_true' );
		remove_filter( 'rocket_mcp_oauth_server_enabled', '__return_true' );
		remove_all_filters( 'rocket_mcp_trusted_publishers' );
		Bootstrap::schedule_rewrite_flush();

		// Reset rewrite state so the ^oauth/authorize rule an enabled test
		// registers and flushes (into the in-memory $wp_rewrite->extra_rules_top
		// and the persisted 'rewrite_rules' option) cannot leak into a later
		// disabled test. WP_Rewrite::init() does not clear these, so reset them
		// explicitly.
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];
		$wp_rewrite->rules           = [];
		delete_option( 'rewrite_rules' ); // @phpstan-ignore custom.rules.discourageOptionUsage

		self::uninstallPreloadCacheTable();
		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();

		parent::tear_down();
	}

	/**
	 * The library must be booted and wired to WordPress once the plugin has loaded:
	 * its 'init' hooks (secret creation at priority 5, lazy rewrite flush at
	 * priority 20) are bound by Bootstrap::instance().
	 *
	 * @return void
	 */
	public function testShouldBootAndWireTheLibrary() {
		$bootstrap = Bootstrap::instance();

		$this->assertSame(
			5,
			has_action( 'init', [ SecretManager::class, 'ensure_secret' ] )
		);
		$this->assertSame(
			20,
			has_action( 'init', [ $bootstrap, 'maybe_flush_rewrite_rules' ] )
		);
	}

	/**
	 * With the server enabled via the new filter, the OAuth authorize endpoint's
	 * rewrite rule must be registered on the first request.
	 *
	 * @return void
	 */
	public function testShouldRouteOauthEndpointWhenEnabled() {
		add_filter( 'wpmedia_mcp_oauth_server_enabled', '__return_true' );

		$this->set_permalink_structure( '/%postname%/' );
		Bootstrap::schedule_rewrite_flush();

		do_action( 'init' );

		global $wp_rewrite;
		$this->assertArrayHasKey( '^oauth/authorize$', $wp_rewrite->wp_rewrite_rules() );
	}

	/**
	 * With the server disabled (the default), the OAuth authorize endpoint must not
	 * be registered — a request to it resolves to a WordPress 404.
	 *
	 * @return void
	 */
	public function testShouldReturn404WhenDisabled() {
		$this->set_permalink_structure( '/%postname%/' );
		Bootstrap::schedule_rewrite_flush();

		do_action( 'init' );

		global $wp_rewrite;
		$this->assertArrayNotHasKey( '^oauth/authorize$', $wp_rewrite->wp_rewrite_rules() );
	}

	/**
	 * A site still using the legacy 'rocket_mcp_oauth_server_enabled' filter must
	 * keep the OAuth server enabled: the library reads the legacy name on top of
	 * the new one, so its value continues to win.
	 *
	 * @return void
	 */
	public function testShouldEnableServerThroughLegacyFilter() {
		// Using the legacy filter must emit the deprecation notice the shim fires
		// on 'init'; declare it as expected so the strict WP test framework does
		// not flag it as an unexpected deprecation.
		$this->setExpectedDeprecated( 'rocket_mcp_oauth_server_enabled' );

		add_filter( 'rocket_mcp_oauth_server_enabled', '__return_true' );

		$this->set_permalink_structure( '/%postname%/' );
		Bootstrap::schedule_rewrite_flush();

		do_action( 'init' );

		global $wp_rewrite;
		$this->assertArrayHasKey( '^oauth/authorize$', $wp_rewrite->wp_rewrite_rules() );
	}

	/**
	 * A site still using the legacy 'rocket_mcp_trusted_publishers' filter must
	 * keep its custom publishers honored by the CIMD trust check: the library's
	 * ClaudeClientVerifier applies the legacy filter name on top of the new
	 * 'wpmedia_mcp_oauth_trusted_publishers' one, so the legacy *value* still
	 * reaches verification through the back-compat bridge.
	 *
	 * The assertion depends on the legacy filter: the injected client_id/host is
	 * absent from the library's default allowlist (only 'claude'), so removing
	 * the filter makes verify()/is_trusted_host() return false and this test fail.
	 *
	 * @return void
	 */
	public function testShouldReachVerifierThroughLegacyTrustedPublishersFilter() {
		// Registering the legacy filter must emit the deprecation notice the shim
		// fires on 'init'; declare it as expected so the strict WP test framework
		// does not flag it as an unexpected deprecation.
		$this->setExpectedDeprecated( 'rocket_mcp_trusted_publishers' );

		$legacy_host      = 'legacy-publisher.example';
		$legacy_client_id = 'https://legacy-publisher.example/oauth/client-metadata';

		add_filter(
			'rocket_mcp_trusted_publishers',
			function ( $publishers ) use ( $legacy_host, $legacy_client_id ) {
				$publishers['legacy_test_publisher'] = [
					'client_ids' => [ $legacy_client_id ],
					'host'       => $legacy_host,
				];

				return $publishers;
			}
		);

		// Run the WP Rocket deprecation shim (normally hooked at 'init' priority 1)
		// so it emits the _deprecated_hook notice for the legacy filter that now
		// has a callback registered.
		( new DeprecatedFilters() )->maybe_notify_deprecated_filters();

		$verifier = new ClaudeClientVerifier();

		// The client_id is a valid public client only because the legacy filter
		// added its publisher config; the library default allowlist does not
		// contain it.
		$result = $verifier->verify(
			$legacy_client_id,
			[ 'token_endpoint_auth_method' => 'none' ]
		);

		$this->assertTrue( $result['verified'] );
		$this->assertSame( 'legacy_test_publisher', $result['publisher'] );

		// The SSRF host gate must also honor the legacy publisher's host.
		$this->assertTrue( $verifier->is_trusted_host( $legacy_client_id ) );
	}
}
