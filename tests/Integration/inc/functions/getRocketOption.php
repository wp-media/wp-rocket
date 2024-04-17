<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::get_rocket_option
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 *
 * @group  Options
 * @group  Functions
 */
class Test_GetRocketOption extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOptionValue( $option, $default, $expected ) {
		$this->assertSame( $expected, get_rocket_option( $option, $default ) );
	}
}
