<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::varnish_addon_title
 *
 * @group  WPEngine
 * @group  ThirdParty
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
