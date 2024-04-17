<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\ThirdParty\Hostings\WPXCloud;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPXCloud::varnish_addon_title
 * @group WPXCloud
 */
class Test_VarnishAddonTitle extends TestCase {
	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayVarnishTitleWithWPXCloud( $settings, $expected ) {

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_field_settings', $settings )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishAddonTitle' );
	}
}
