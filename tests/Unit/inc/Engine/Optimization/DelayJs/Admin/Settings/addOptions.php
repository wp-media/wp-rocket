<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJs\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::add_options
 *
 * @group  DelayJs
 */
class Test_AddOptions extends TestCase{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];

		$options_data = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options_data );
		$actual = $settings->add_options($options);

		$this->assertSame( $expected, $actual );

	}

}
