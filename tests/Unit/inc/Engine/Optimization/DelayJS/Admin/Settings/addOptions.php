<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::add_options
 *
 * @group  DelayJS
 */
class Test_AddOptions extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		$options  = isset( $input['options'] )  ? $input['options']  : [];
		$settings = new Settings();

		$this->assertSame(
			$expected,
			$settings->add_options( $options )
		);
	}
}
