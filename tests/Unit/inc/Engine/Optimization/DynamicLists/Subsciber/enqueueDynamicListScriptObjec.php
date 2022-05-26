<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::enqueue_admin_edit_script
 *
 * @group  DynamicLists
 */
class Test_EnqueueDynamicListScriptObject extends TestCase {

	public function testShouldEnqueueDynamicListScriptObject() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );
		Functions\expect( 'rest_url' )
			->once()
			->with( "wp-rocket/v1/wpr-dynamic-lists/" )
			->andReturn( "http://example.org/wp-rocket/v1/wpr-dynamic-lists/" );

		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'wp_rest' )
			->andReturn( 'wp_rest_nonce' );

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'wpr-admin',
				'rocket_dynamic_lists',
				[
					'rest_url'   => 'http://example.org/wp-rocket/v1/wpr-dynamic-lists/',
					'rest_nonce' => 'wp_rest_nonce',
				]
			)
			->andReturnNull();
		$subscriber->add_dynamic_lists_script();
	}
}
