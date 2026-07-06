<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Activation\Activation;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Engine\Activation\ServiceProvider as ActivationServiceProvider;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints;
use WP_Rocket\Engine\MCP\Auth\Rewrite;
use WP_Rocket\Engine\MCP\Auth\SecretManager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering the MCP OAuth activation wiring in
 * \WP_Rocket\Engine\Activation\Activation::activate_plugin().
 *
 * Regression test for #8558: on first plugin activation, both the MCP Auth
 * discovery rewrite rules and the OAuth rewrite rules must be registered
 * (via ActivationInterface, dispatched by the container inflector) before
 * Activation::activate_plugin()'s single trailing flush_rewrite_rules().
 *
 * @group Activation
 * @group MCP
 */
class ActivatePluginTest extends TestCase {

	/**
	 * Isolated container replicating the Activation::activate_plugin() setup.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Discovery endpoints resolved from the container.
	 *
	 * @var mixed
	 */
	private $discovery_endpoints;

	/**
	 * OAuth rewrite registration resolved from the container.
	 *
	 * @var mixed
	 */
	private $rewrite;

	/**
	 * Secret manager resolved from the container.
	 *
	 * @var mixed
	 */
	private $secret_manager;

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

		// Replicates the isolated container built inside Activation::activate_plugin():
		// registering the Activation ServiceProvider both provides the MCP Auth
		// activation services and boots the ActivationInterface inflector, without
		// invoking the full method (which performs real HTTP/filesystem side effects
		// unrelated to this fix).
		$this->container = new Container();
		$this->container->add( 'template_path', new StringArgument( rocket_get_constant( 'WP_ROCKET_PATH', '' ) . 'views' ) );
		$this->container->addServiceProvider( new ActivationServiceProvider() );

		$this->discovery_endpoints = $this->container->get( 'mcp_auth_discovery_endpoints' );
		$this->rewrite             = $this->container->get( 'mcp_auth_rewrite' );
		$this->secret_manager      = $this->container->get( 'mcp_secret_manager' );
	}

	/**
	 * Remove hooks registered during the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_action( 'rocket_activation', [ $this->discovery_endpoints, 'add_rewrite_rules' ] );
		remove_action( 'rocket_activation', [ $this->rewrite, 'register_oauth_rewrite_rules' ] );
		remove_action( 'rocket_activation', [ SecretManager::class, 'ensure_secret' ] );

		self::uninstallPreloadCacheTable();
		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();

		parent::tear_down();
	}

	/**
	 * The container must resolve all three MCP Auth activation services without error.
	 *
	 * @return void
	 */
	public function testContainerResolvesMcpAuthActivationServicesWithoutError() {
		$this->assertInstanceOf( Endpoints::class, $this->discovery_endpoints );
		$this->assertInstanceOf( Rewrite::class, $this->rewrite );
		$this->assertInstanceOf( SecretManager::class, $this->secret_manager );
	}

	/**
	 * Resolving the services from the container must dispatch their activate()
	 * method via the ActivationInterface inflector, registering their callbacks
	 * on rocket_activation.
	 *
	 * @return void
	 */
	public function testShouldRegisterMcpAuthRewriteRulesOnRocketActivation() {
		$this->assertNotFalse(
			has_action( 'rocket_activation', [ $this->discovery_endpoints, 'add_rewrite_rules' ] )
		);
		$this->assertNotFalse(
			has_action( 'rocket_activation', [ $this->rewrite, 'register_oauth_rewrite_rules' ] )
		);
		$this->assertNotFalse(
			has_action( 'rocket_activation', [ SecretManager::class, 'ensure_secret' ] )
		);
	}

	/**
	 * After rocket_activation fires and Activation::activate_plugin()'s trailing
	 * flush runs, the resulting flush must persist both the .well-known discovery
	 * rules and the /oauth/* rules to the rewrite_rules option — not just fire the
	 * hooks in the right order.
	 *
	 * @return void
	 */
	public function testShouldPersistDiscoveryAndOauthRewriteRulesAfterActivationFlush() {
		$this->set_permalink_structure( '/%postname%/' );

		do_action( 'rocket_activation' );
		flush_rewrite_rules();

		global $wp_rewrite;
		$rewrite_rules = $wp_rewrite->wp_rewrite_rules();

		$this->assertIsArray( $rewrite_rules );
		$this->assertArrayHasKey( '^\\.well-known/oauth-protected-resource$', $rewrite_rules );
		$this->assertArrayHasKey( '^\\.well-known/oauth-authorization-server$', $rewrite_rules );
		$this->assertArrayHasKey( '^oauth/authorize$', $rewrite_rules );
		$this->assertArrayHasKey( '^oauth/token$', $rewrite_rules );
	}
}
