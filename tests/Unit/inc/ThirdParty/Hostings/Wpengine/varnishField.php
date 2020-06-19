<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::varnish_field
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_VarnishField extends WpengineTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$this->assertSame(
			$expected,
			$this->wpengine->varnish_field( $settings )
		);
	}
}
