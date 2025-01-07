<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::add_options
 *
 * @group  DelayJS
 */
class Test_AddOptions extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		$options  = isset( $input['options'] )  ? $input['options']  : [];
		$settings = new Settings( Mockery::mock(Options::class) );

		$this->assertSame(
			$expected,
			$settings->add_options( $options )
		);
	}
}
