<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\ThirstyAffiliates;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates::is_activated
 *
 * Under Brain\Monkey, function_exists( 'is_plugin_active' ) resolves true, so
 * the require_once ABSPATH . 'wp-admin/includes/plugin.php' boot-timing guard
 * is skipped in-test; no process isolation is needed.
 *
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	public function testShouldReturnFalseWhenPluginDeactivated() {
		Functions\when( 'is_plugin_active' )->justReturn( false );

		$this->assertFalse( ThirstyAffiliates::is_activated() );
	}

	public function testShouldReturnTrueWhenPluginActive() {
		Functions\when( 'is_plugin_active' )->justReturn( true );

		$this->assertTrue( ThirstyAffiliates::is_activated() );
	}
}
