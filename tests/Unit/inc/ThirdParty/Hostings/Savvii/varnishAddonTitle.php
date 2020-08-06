<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Savvii;

use WP_Rocket\ThirdParty\Hostings\Savvii;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Savvii::varnish_addon_title
 * @group Savvii
 * @group ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayVarnishTitleWithSavvii( $settings, $expected ) {
		$savvii = new Savvii();

		$this->assertSame(
			$expected,
			$savvii->varnish_addon_title( $settings )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishAddonTitle' );
	}
}
