<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::register_routes
 *
 * @group RocketCDN
 */
class Test_RegisterRoutes extends ApiTestCase {

	/**
	 * Test should not register the removed enable and disable routes.
	 */
	public function testShouldNotRegisterRemovedRoutes() {
		$routes = $this->server->get_routes();

		$this->assertArrayNotHasKey( '/wp-rocket/v1/rocketcdn/enable', $routes );
		$this->assertArrayNotHasKey( '/wp-rocket/v1/rocketcdn/disable', $routes );
	}

	/**
	 * Test should still register the remaining routes.
	 */
	public function testShouldRegisterRemainingRoutes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/wp-rocket/v1/support', $routes );
		$this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/driver', $routes );
		$this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/pages', $routes );
	}
}
