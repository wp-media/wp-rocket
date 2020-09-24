<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Dreampress;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::set_varnish_addon_title
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_SetVarnishAddonTitle extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$dreampress = new Dreampress();

		$this->assertSame(
			$expected,
			$dreampress->set_varnish_addon_title( $settings )
		);
	}
}
