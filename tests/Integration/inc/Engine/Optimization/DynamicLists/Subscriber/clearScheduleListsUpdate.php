<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::clear_schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ClearScheduleListsUpdate extends TestCase {
	public function testShouldDoExpected() {
		wp_schedule_event( time(), WEEK_IN_SECONDS, 'rocket_update_dynamic_lists' );

		do_action( 'rocket_deactivation' );

		$this->assertFalse( wp_next_scheduled( 'rocket_update_dynamic_lists' ) );
	}
}
