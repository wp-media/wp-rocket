<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists;


use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ScheduleListsUpdate extends TestCase {
	public function testShouldDoExpected() {
		$options           = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) );
		$dynamic_lists_api = new APIClient($options);
		$data_manager      = new DataManager();
		$dynamic_lists = new DynamicLists( $dynamic_lists_api, $data_manager, '' );
		$dynamic_lists->schedule_lists_update();

		$this->assertNotFalse( wp_next_scheduled( 'rocket_update_dynamic_lists' ) );

		wp_clear_scheduled_hook( 'rocket_update_dynamic_lists' );
	}
}
