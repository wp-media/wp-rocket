<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Unit\TestCase;
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
		$o2switch = new O2Switch();

		$this->assertSame(
			$expected,
			$o2switch->varnish_addon_title( $settings )
		);
	}
}
