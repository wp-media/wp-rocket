<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::clear_schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ClearScheduleListsUpdate extends TestCase {
	public function testShouldDoExpect() {
		$dynamic_lists = new DynamicLists( Mockery::mock( APIClient::class ), Mockery::mock( DataManager::class ), Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ) );

		Functions\expect( 'wp_clear_scheduled_hook' )
			->with( 'rocket_update_dynamic_lists' )
			->once();

		$dynamic_lists->clear_schedule_lists_update();
	}
}
