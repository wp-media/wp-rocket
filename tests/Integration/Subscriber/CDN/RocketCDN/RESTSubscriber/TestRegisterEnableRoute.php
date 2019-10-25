<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use PHPUnit\Framework\TestCase;

class TestRegisterEnableRoute extends TestCase {
    public function setUp() {
		parent::setUp();
		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
 
    }

    public function testEnableRouteIsRegistered() {
        $routes = $this->server->get_routes();
        $this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/enable', $routes );
    }

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
