<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Cloudways;

use WP_Rocket\ThirdParty\Hostings\Cloudways;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Cloudways::varnish_addon_title
 * @group Cloudways
 * @group ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayVarnishTitleWithCloudways( $settings, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_field_settings', $settings )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishAddonTitle' );
	}
}
