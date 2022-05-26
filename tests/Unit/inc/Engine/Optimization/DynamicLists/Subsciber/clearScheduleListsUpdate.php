<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::clear_schedule_lists_update
 *
 * @group  DynamicLists
 */
class Test_ClearScheduleListsUpdate extends TestCase {

	public function testShouldEnqueueDynamicListScriptObject() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );
		$dynamic_lists
			->shouldReceive( 'clear_schedule_lists_update' )
			->once()
			->andReturnNull();
		$subscriber->clear_schedule_lists_update();
	}
}
