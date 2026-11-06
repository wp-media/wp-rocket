<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Ads\Adthrive;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Ads\Adthrive::add_delay_js_exclusion
 *
 * @group Adthrive
 * @group ThirdParty
 */
class Test_AddDelayJsExclusion extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$adthrive = new Adthrive();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wp_rocket_settings', [] )
			->andReturn( $settings );

		Functions\expect( 'update_option' )
			->atMost()
			->once()
			->with( 'wp_rocket_settings', $expected );

		$adthrive->add_delay_js_exclusion();
	}
}
