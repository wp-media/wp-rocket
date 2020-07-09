<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_addon_title
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_field_settings', $settings )
		);
	}
}
