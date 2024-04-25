<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Engine\Optimization\DynamicLists\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_dynamic_lists_script
 *
 * @group  DynamicLists
 */
class Test_AddDynamicListsScripts extends TestCase {

	public function testShouldReturnExpected() {
		$dynamic_lists = Mockery::mock( DynamicLists::class );
		$subscriber    = new Subscriber( $dynamic_lists );

		Functions\expect( 'rest_url' )
			->once()
			->with( "wp-rocket/v1/dynamic_lists/update/" )
			->andReturn( 'http://example.org/wp-json/wp-rocket/v1/dynamic_lists/update/' );

		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'wp_rest' )
			->andReturn( 'wp_rest_nonce' );

		$this->assertEquals(
			[
				'rest_url' => 'http://example.org/wp-json/wp-rocket/v1/dynamic_lists/update/',
				'rest_nonce' => 'wp_rest_nonce',
			],
			$subscriber->add_dynamic_lists_script( [] )
		);
	}
}
