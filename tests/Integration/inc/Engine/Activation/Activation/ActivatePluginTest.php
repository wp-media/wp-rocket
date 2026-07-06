<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Activation\Activation;

use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Engine\MCP\Auth\ServiceProvider as McpAuthServiceProvider;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering the MCP OAuth subscriber wiring added to
 * \WP_Rocket\Engine\Activation\Activation::activate_plugin().
 *
 * Regression test for #8558: on first plugin activation, the isolated
 * container built by Activation::activate_plugin() must register both
 * `mcp_auth_discovery_subscriber` and `mcp_auth_subscriber` on
 * `rocket_activation`, with the discovery subscriber added first so its
 * rewrite rules exist in memory before the auth subscriber flushes them.
 *
 * @group Activation
 * @group MCP
 */
class ActivatePluginTest extends TestCase {

	/**
	 * Event manager under test.
	 *
	 * @var Event_Manager
	 */
	private $event_manager;

	/**
	 * Discovery subscriber resolved from the container.
	 *
	 * @var mixed
	 */
	private $discovery_subscriber;

	/**
	 * Auth subscriber resolved from the container.
	 *
	 * @var mixed
	 */
	private $auth_subscriber;

	/**
	 * Set up the isolated container the same way Activation::activate_plugin() does.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Changing the permalink structure fires 'permalink_structure_changed', which
		// PerformanceHints subscribers listen to and use to truncate their tables.
		// Install them so that hook doesn't hit missing-table DB errors during the test.
		self::installPreloadCacheTable();
		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();

		// Replicates the isolated container + event manager built inside
		// Activation::activate_plugin() for the MCP Auth wiring specifically,
		// without invoking the full method (which performs real HTTP/filesystem
		// side effects unrelated to this fix).
		$container = new Container();
		$container->addServiceProvider( new McpAuthServiceProvider() );

		$this->event_manager        = new Event_Manager();
		$this->discovery_subscriber = $container->get( 'mcp_auth_discovery_subscriber' );
		$this->auth_subscriber      = $container->get( 'mcp_auth_subscriber' );
	}

	/**
	 * Remove subscribers registered during the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->event_manager->remove_subscriber( $this->discovery_subscriber );
		$this->event_manager->remove_subscriber( $this->auth_subscriber );

		self::uninstallPreloadCacheTable();
		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();

		parent::tear_down();
	}

	/**
	 * The container must resolve both MCP Auth subscribers without error.
	 *
	 * @return void
	 */
	public function testContainerResolvesBothMcpAuthSubscribersWithoutError() {
		$this->assertInstanceOf(
			'WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber',
			$this->discovery_subscriber
		);
		$this->assertInstanceOf(
			'WP_Rocket\Engine\MCP\Auth\Subscriber',
			$this->auth_subscriber
		);
	}

	/**
	 * The discovery subscriber must run before the auth subscriber on rocket_activation.
	 *
	 * @return void
	 */
	public function testShouldRegisterDiscoverySubscriberBeforeAuthSubscriberOnRocketActivation() {
		$this->event_manager->add_subscriber( $this->discovery_subscriber );
		$this->event_manager->add_subscriber( $this->auth_subscriber );

		// Ordering is enforced by explicit hook priority (not add order): the discovery
		// subscriber's rewrite rules must be in memory before the auth subscriber flushes them.
		$this->assertSame(
			5,
			has_action( 'rocket_activation', [ $this->discovery_subscriber, 'add_rewrite_rules' ] )
		);
		$this->assertSame(
			20,
			has_action( 'rocket_activation', [ $this->auth_subscriber, 'on_activation' ] )
		);
	}

	/**
	 * After rocket_activation fires, the resulting flush must persist both the
	 * .well-known discovery rules and the /oauth/* rules to the rewrite_rules
	 * option — not just fire the hooks in the right order.
	 *
	 * @return void
	 */
	public function testShouldPersistDiscoveryAndOauthRewriteRulesAfterActivationFlush() {
		$this->set_permalink_structure( '/%postname%/' );

		$this->event_manager->add_subscriber( $this->discovery_subscriber );
		$this->event_manager->add_subscriber( $this->auth_subscriber );

		do_action( 'rocket_activation' );

		global $wp_rewrite;
		$rewrite_rules = $wp_rewrite->wp_rewrite_rules();

		$this->assertIsArray( $rewrite_rules );
		$this->assertArrayHasKey( '^\\.well-known/oauth-protected-resource$', $rewrite_rules );
		$this->assertArrayHasKey( '^\\.well-known/oauth-authorization-server$', $rewrite_rules );
		$this->assertArrayHasKey( '^oauth/authorize$', $rewrite_rules );
		$this->assertArrayHasKey( '^oauth/token$', $rewrite_rules );
	}
}
