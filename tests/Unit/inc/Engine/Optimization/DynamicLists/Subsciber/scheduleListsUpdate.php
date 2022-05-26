<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ScheduleListsUpdate extends TestCase {

	public function testShouldEnqueueDynamicListScriptObject() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );

		/*$dynamic_lists_api = Mockery::mock( APIClient::class );
		$dynamic_lists= new DynamicLists($dynamic_lists_api);
		$subscriber    = new Subscriber( $dynamic_lists );*/
		$dynamic_lists
			->shouldReceive( 'schedule_lists_update' )
			->once()
			->andReturnNull();
		/*$timestamp = time();
		$after_week=strtotime($timestamp."+1 week");*/
		/*Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocket_update_dynamic_lists' )
			->andReturn( $after_week );*/

		/*Functions\expect( 'wp_schedule_event' )
			->once()
			->with( $timestamp, 'weekly', 'rocket_update_dynamic_lists' )
			->andReturnNull();*/

		$subscriber->schedule_lists_update();
		/*$this->assertEquals( $after_week, wp_next_scheduled('rocket_update_dynamic_lists') );*/
	}
}
