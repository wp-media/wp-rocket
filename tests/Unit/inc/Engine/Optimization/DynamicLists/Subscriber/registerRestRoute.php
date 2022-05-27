<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::register_rest_route
 *
 * @group  DynamicLists
 */
class Test_RegisterRestRoute extends TestCase {

	public function testShouldRegisterRestRoute() {
		$dynamic_lists_api = Mockery::mock( APIClient::class );
		$dynamic_lists= new DynamicLists($dynamic_lists_api);
		$subscriber    = new Subscriber( $dynamic_lists );
		Functions\expect( 'register_rest_route' )
			->once()
			->with( $dynamic_lists::ROUTE_NAMESPACE,
				'wpr-dynamic-lists',
				[
					'methods'             => 'PUT',
					'callback'            => [ $dynamic_lists, 'rest_update_response' ],
					'permission_callback' => [ $dynamic_lists, 'check_permissions' ],
				] )
			->andReturnNull();

		$subscriber->register_rest_route();
	}
}
