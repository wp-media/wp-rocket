<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPEngine::varnish_addon_title
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_varnishAddonTitle extends WPEngineTestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$this->assertSame(
			$expected,
			$this->wpengine->varnish_addon_title( $settings )
		);
	}
}
