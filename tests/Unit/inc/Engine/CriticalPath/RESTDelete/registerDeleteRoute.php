<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTDelete;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\RESTDelete;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::register_delete_route
 * @group  CriticalPath
 */
class Test_RegisterDeleteRouter extends TestCase {

	public function testShouldRegisterRoute() {
		$instance = new RESTDelete();

		Functions\expect( 'register_rest_route' )
			->once()
			->with(
				RESTDelete::ROUTE_NAMESPACE,
				'cpcss/post/(?P<id>[\d]+)',
				[
					'methods'             => 'DELETE',
					'callback'            => [ $instance, 'delete' ],
					'permission_callback' => [ $instance, 'check_permissions' ],
				]
			);

		$instance->register_delete_route();
	}
}
