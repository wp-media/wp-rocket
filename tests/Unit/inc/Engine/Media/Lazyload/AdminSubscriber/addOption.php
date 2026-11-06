<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\AdminSubscriber;

use WP_Rocket\Engine\Media\Lazyload\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\AdminSubscriber::add_option
 *
 * @group Media
 * @group Lazyload
 */
class Test_AddOption extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $input, $expected ) {
		$options    = isset( $input['options'] )  ? $input['options']  : [];
		$subscriber = new AdminSubscriber();

		$this->assertSame(
			$expected,
			$subscriber->add_option( $options )
		);
	}
}
