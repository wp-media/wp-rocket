<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTGenerate;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\RESTGenerate;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTGenerate::register_generate_route
 *
 * @group  CriticalPath
 */
class Test_RegisterGenerateRoute extends TestCase {

	public function testShouldRegisterRoute() {
		Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );

		$instance = new RESTGenerate( 'wp-content/cache/critical-css/' );

		Functions\expect( 'register_rest_route' )
			->once()
			->with(
				RESTGenerate::ROUTE_NAMESPACE,
				'cpcss/post/(?P<id>[\d]+)',
				[
					'methods'             => 'POST',
					'callback'            => [ $instance, 'generate' ],
					'permission_callback' => [ $instance, 'check_permissions' ],
				]
			);

		$instance->register_generate_route();
	}
}
