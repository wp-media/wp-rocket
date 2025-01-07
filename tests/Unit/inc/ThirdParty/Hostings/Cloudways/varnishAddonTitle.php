<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Cloudways;

use WP_Rocket\ThirdParty\Hostings\Cloudways;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Cloudways::varnish_addon_title
 * @group Cloudways
 * @group ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	protected function tearDown(): void {
		// Reset after each test.
		unset( $_SERVER['HTTP_X_VARNISH'] );
		unset( $_SERVER['HTTP_X_APPLICATION'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayVarnishTitleWithCloudways( $settings, $config_server,$expected ) {
		if ( isset( $config_server['HTTP_X_VARNISH'] ) ) {
			$_SERVER['HTTP_X_VARNISH'] = $config_server['HTTP_X_VARNISH'];
		}
		if ( isset( $config_server['HTTP_X_APPLICATION'] ) ) {
			$_SERVER['HTTP_X_APPLICATION'] = $config_server['HTTP_X_APPLICATION'];
		}

		$cloudways = new Cloudways();

		$this->assertSame(
			$expected,
			$cloudways->varnish_addon_title( $settings )
		);
	}
}
