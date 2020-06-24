<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::varnish_addon_title
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_varnishAddonTitle extends WPEngineTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

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
