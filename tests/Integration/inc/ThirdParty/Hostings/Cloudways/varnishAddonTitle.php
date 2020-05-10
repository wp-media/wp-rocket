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
	public function testShouldDisplayVarnishTitleWithCloudways() {
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
			apply_filters( 'rocket_varnish_field_settings', $settings )
		);
	}
}
