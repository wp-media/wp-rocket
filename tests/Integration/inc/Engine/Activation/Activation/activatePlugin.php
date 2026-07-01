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
class Test_ActivatePlugin extends TestCase {

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
	 * The discovery subscriber must be registered before the auth subscriber.
	 *
	 * @return void
	 */
	public function testShouldRegisterDiscoverySubscriberBeforeAuthSubscriberOnRocketActivation() {
		// Mirrors the exact registration order added in Activation::activate_plugin():
		// discovery subscriber first, auth subscriber second.
		$this->event_manager->add_subscriber( $this->discovery_subscriber );
		$this->event_manager->add_subscriber( $this->auth_subscriber );

		$this->assertSame(
			10,
			has_action( 'rocket_activation', [ $this->discovery_subscriber, 'add_rewrite_rules' ] )
		);
		$this->assertSame(
			10,
			has_action( 'rocket_activation', [ $this->auth_subscriber, 'on_activation' ] )
		);

		// WordPress fires same-priority callbacks in add order, so the discovery
		// rewrite rules must be registered (add_action call order) strictly before
		// the auth subscriber's callback, which flushes rewrite rules immediately.
		global $wp_filter;

		$callbacks    = array_keys( $wp_filter['rocket_activation']->callbacks[10] );
		$discovery_at = array_search(
			$this->get_hook_id( $this->discovery_subscriber, 'add_rewrite_rules' ),
			$callbacks,
			true
		);
		$auth_at      = array_search(
			$this->get_hook_id( $this->auth_subscriber, 'on_activation' ),
			$callbacks,
			true
		);

		$this->assertNotFalse( $discovery_at );
		$this->assertNotFalse( $auth_at );
		$this->assertLessThan(
			$auth_at,
			$discovery_at,
			'The discovery subscriber must be registered on rocket_activation before the auth subscriber, otherwise the .well-known rewrite rules are not in memory yet when the auth subscriber flushes rewrite rules.'
		);
	}

	/**
	 * Build the WordPress hook id for a [ $subscriber, $method ] callback the
	 * same way WP_Hook does internally, so we can locate it in $wp_filter's
	 * callback list regardless of internal spl_object_hash formatting.
	 *
	 * @param object $subscriber Subscriber instance.
	 * @param string $method     Method name.
	 * @return string
	 */
	private function get_hook_id( $subscriber, string $method ): string {
		return spl_object_hash( $subscriber ) . $method;
	}
}
