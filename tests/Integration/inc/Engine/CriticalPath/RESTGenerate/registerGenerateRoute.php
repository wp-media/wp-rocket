<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTGenerate;

use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTGenerate::register_generate_route
 *
 * @group  CriticalPath
 */
class Test_RegisterGenerateRoute extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTGenerate/generate.php';

	/**
	 * Test should register the generate route with the WP REST API.
	 */
	public function testShouldRegisterRoute() {
		$this->assertArrayHasKey( '/wp-rocket/v1/cpcss/post/(?P<id>[\d]+)', $this->server->get_routes() );
	}
}
