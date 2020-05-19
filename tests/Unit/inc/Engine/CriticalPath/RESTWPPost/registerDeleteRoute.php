<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTWPPost;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Engine\CriticalPath\RESTWPPost;
use WPMedia\PHPUnit\Unit\TestCase;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTWPPost::register_delete_route
 * @group  CriticalPath
 */
class Test_RegisterDeleteRoute extends TestCase {

	public function testShouldRegisterRoute() {
		$api_client = Mockery::mock( APIClient::class );
		$data_manager = Mockery::mock( DataManager::class );
		$instance = new RESTWPPost( $data_manager, $api_client );

		Functions\expect( 'register_rest_route' )
			->once()
			->with(
				RESTWPPost::ROUTE_NAMESPACE,
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
