<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_async_css_mobile_option
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_AddAsyncCssMobileOption extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_first_install_options', $options )
		);
	}
}
