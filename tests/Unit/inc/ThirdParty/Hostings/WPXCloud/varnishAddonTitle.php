<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\ThirdParty\Hostings\WPXCloud;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPXCloud::varnish_addon_title
 * @group WPXCloud
 */
class Test_VarnishAddonTitle extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayVarnishTitleWithWPXCloud( $settings, $expected ) {
		$wpx_cloud = new WPXCloud();

		$this->assertSame(
			$expected,
			$wpx_cloud->varnish_addon_title( $settings )
		);
	}
}
