<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Ads\Adthrive;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ads\Adthrive::maybe_add_delay_js_exclusion
 *
 * @group Adthrive
 * @group ThirdParty
 */
class Test_MaybeAddDelayJsExclusion extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$adthrive = new Adthrive();

		Functions\when('is_plugin_active')->justReturn( $settings['plugin_active'] );

		$this->assertSame(
			$expected,
			$adthrive->maybe_add_delay_js_exclusion( $settings['value'], $settings['old_value'] )
		);
	}
}
