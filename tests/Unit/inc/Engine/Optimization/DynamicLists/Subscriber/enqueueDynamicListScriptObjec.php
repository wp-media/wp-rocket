<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::add_dynamic_lists_script
 *
 * @group  DynamicLists
 */
class Test_EnqueueDynamicListScriptObject extends TestCase {

	public function testShouldEnqueueDynamicListScriptObject() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );
		Functions\expect( 'rest_url' )
			->once()
			->with( "wp-rocket/v1/dynamic_lists/update/" )
			->andReturn( "http://example.org/wp-rocket/v1/dynamic_lists/update/" );

		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'wp_rest' )
			->andReturn( 'wp_rest_nonce' );
		$subscriber->add_dynamic_lists_script([]);
	}
}
