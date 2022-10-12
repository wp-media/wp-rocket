<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::add_option
 *
 * @group  DeferJS
 */
class Test_AddOption extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];
		$defer_js = new DeferJS( Mockery::mock( Options_Data::class ), Mockery::mock( DataManager::class ) );

		$this->assertSame(
			$expected,
			$defer_js->add_option( $options )
		);
	}
}
