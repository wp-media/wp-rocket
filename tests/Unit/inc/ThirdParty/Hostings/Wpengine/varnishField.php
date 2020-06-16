<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Wpengine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::varnish_field
 * @group Wpengine
 * @group ThirdParty
 */
class Test_VarnishField extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$wpengine = new Wpengine();

		$this->assertSame(
			$expected,
			$wpengine->varnish_field( $settings )
		);
	}
}
