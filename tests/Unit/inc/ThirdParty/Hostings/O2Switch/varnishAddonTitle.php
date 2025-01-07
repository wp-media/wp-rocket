<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_addon_title
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

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
