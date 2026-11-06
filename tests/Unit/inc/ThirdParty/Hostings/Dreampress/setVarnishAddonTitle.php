<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Dreampress;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Dreampress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Dreampress::set_varnish_addon_title
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_SetVarnishAddonTitle extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

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
