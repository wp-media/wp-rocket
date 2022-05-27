<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::update_lists
 *
 * @group  DynamicLists
 */
class Test_UpdateDynamicLists extends TestCase {

	public function testShouldEnqueueDynamicListScriptObject() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );
		$dynamic_lists
			->shouldReceive( 'update_lists_from_remote' )
			->once()
			->andReturnNull();
		$subscriber->update_lists();
	}
}
