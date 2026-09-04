<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::is_activated
 *
 * Presence-only semantics: is_activated() must reflect plugin presence alone,
 * never the saved Cloudflare credentials checked by the private
 * is_plugin_active() method used elsewhere in the class (issue #8790).
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_isActivated extends TestCase {

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when( 'is_plugin_active' )->justReturn( $config['plugin_active'] );
		// Locks in presence-only semantics: is_activated() must never consult
		// the saved Cloudflare credentials, regardless of plugin activation state.
		Functions\expect( 'get_option' )->never();

		$this->assertSame( $expected, Cloudflare::is_activated() );
	}
}
