<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::add_options
 *
 * @group  DelayJs
 */
class Test_AddOptions extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForFirstInstallOptions( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];

		$actual = apply_filters( 'rocket_first_install_options', $options );

		$this->assertSame( $expected, $actual );

	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForSafeModeResetOptions( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];

		$actual = apply_filters( 'rocket_safe_mode_reset_options', $options );

		$this->assertSame( $expected, $actual );

	}

}
