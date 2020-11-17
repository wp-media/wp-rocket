<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\ImagesDimensions\ImagesDimensions;

use WP_Rocket\Engine\Media\ImagesDimensions\ImagesDimensions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImagesDimensions\ImagesDimensions::add_option
 *
 * @group  ImagesDimensions
 */
class Test_AddOption extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options    = isset( $input['options'] )  ? $input['options']  : [];
		$dimensions = new ImagesDimensions();

		$this->assertSame(
			$expected,
			$dimensions->add_option( $options )
		);
	}
}
