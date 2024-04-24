<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists::register_rest_route
 *
 * @group  DynamicLists
 */
class Test_RegisterRestRoute extends TestCase {

	public function testShouldRegisterRestRoute() {
		$dynamic_lists_api = Mockery::mock( APIClient::class );
		$data_manager = Mockery::mock( DataManager::class );
		$providers = [
			'defaultlists' =>
				(object) [
					'api_client' => $dynamic_lists_api,
					'data_manager' => $data_manager,
				],
		];
		$dynamic_lists = new DynamicLists( $providers, Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ));

		Functions\expect( 'register_rest_route' )
			->once()
			->with( $dynamic_lists::ROUTE_NAMESPACE,
				'dynamic_lists/update',
				[
					'methods'             => 'PUT',
					'callback'            => [ $dynamic_lists, 'rest_update_response' ],
					'permission_callback' => [ $dynamic_lists, 'check_permissions' ],
				] );

		$dynamic_lists->register_rest_route();
	}
}
