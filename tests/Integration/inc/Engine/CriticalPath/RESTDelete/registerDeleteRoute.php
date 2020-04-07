<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTDelete;

use WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RestTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::register_delete_route
 * @group  CriticalPath
 */
class Test_RegisterDeleteRouter extends RestTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTDelete/delete.php';

	/**
	 * Test should register the disable route with the WP REST API.
	 */
	public function testShouldRegisterRoute() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wp-rocket/v1/cpcss/post/(?P<id>[\d]+)', $routes );
	}
}
