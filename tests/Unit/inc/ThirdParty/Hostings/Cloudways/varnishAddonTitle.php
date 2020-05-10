<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Cloudways;

use WP_Rocket\ThirdParty\Hostings\Cloudways;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Cloudways::varnish_addon_title
 * @group Cloudways
 * @group ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	public function testShouldDisplayVarnishTitleWithCloudways() {
		$cloudways = new Cloudways();

		$settings = [
			'varnish_auto_purge' => [
				'title' => '',
			],
		];

		$expected = [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on Cloudways, we have enabled Varnish auto-purge for compatibility.'
			],
		];

		$this->assertSame(
			$expected,
			$cloudways->varnish_addon_title( $settings )
		);
	}
}
