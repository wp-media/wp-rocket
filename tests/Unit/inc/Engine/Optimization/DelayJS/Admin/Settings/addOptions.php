<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::add_options
 *
 * @group  DelayJS
 */
class Test_AddOptions extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];
		$settings = new Settings( Mockery::mock( Options_Data::class ) );

		$this->assertSame(
			$expected,
			$settings->add_options( $options )
		);
	}
}
