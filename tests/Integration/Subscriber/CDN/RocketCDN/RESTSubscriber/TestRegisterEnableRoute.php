<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::register_enable_route
 * @group RocketCDN
 */
class TestRegisterEnableRoute extends TestCase {
	/**
	 * Setup the WP REST API Server
	 */
    public function setUp() {
		parent::setUp();
		/**
		 * @var WP_REST_Server $wp_rest_server
		 * */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
 
	}

	/**
	 * Test that the enable route is correctly registered in the WP REST API
	 */
    public function testRouteIsRegistered() {
        $routes = $this->server->get_routes();
        $this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/enable', $routes );
    }

	/**
	 * Test that the enable route array endpoint contains the callbacks and they are callable
	 */
    public function testEndpoint() {
        $routes = $this->server->get_routes();

        foreach( $routes as $route => $route_config ) {
			if( 0 === strpos( '/wp-rocket/v1/rocketcdn/enable', $route ) ) {
				$this->assertTrue( is_array( $route_config ) );
				foreach( $route_config as $i => $endpoint ) {
					$this->assertArrayHasKey( 'callback', $endpoint );
					$this->assertArrayHasKey( 0, $endpoint[ 'callback' ], get_class( $this ) );
					$this->assertArrayHasKey( 1, $endpoint[ 'callback' ], get_class( $this ) );
					$this->assertTrue( is_callable( array( $endpoint[ 'callback' ][0], $endpoint[ 'callback' ][1] ) ) );
				}
			}
		}
    }
}
