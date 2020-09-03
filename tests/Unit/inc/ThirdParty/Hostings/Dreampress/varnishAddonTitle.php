<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Dreampress;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::varnish_addon_title
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$dreampress = new Dreampress();

		$this->assertSame(
			$expected,
			$dreampress->varnish_addon_title( $settings )
		);
	}
}
