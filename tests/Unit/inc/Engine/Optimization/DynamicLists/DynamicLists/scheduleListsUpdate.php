<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ScheduleListsUpdate extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $scheduled ) {
		$providers = [
			'defaultlists' =>
				(object) [
					'api_client'   => Mockery::mock( APIClient::class ),
					'data_manager' => Mockery::mock( DataManager::class ),
				],
		];
		$dynamic_lists = new DynamicLists( $providers, Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ) );

		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocket_update_dynamic_lists' )
			->andReturn( $scheduled );

		if ( $scheduled ) {
			Functions\expect( 'wp_schedule_event' )
				->never();
		} else {
			Functions\expect( 'wp_schedule_event' )
				->once()
				->with( Mockery::type( 'int' ), 'weekly', 'rocket_update_dynamic_lists' );
		}

		$dynamic_lists->schedule_lists_update();
	}
}
