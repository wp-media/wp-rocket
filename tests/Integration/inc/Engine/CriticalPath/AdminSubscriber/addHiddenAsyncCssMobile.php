<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_hidden_async_css_mobile
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_hidden_settings_fields', $hidden_fields )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addHiddenAsyncCssMobile' );
	}
}
