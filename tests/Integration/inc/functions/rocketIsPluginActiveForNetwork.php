<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::rocket_is_plugin_active_for_network
 * @group  Options
 * @group  Functions
 * @group  Multisite
 * @group  thisone
 */
class Test_RocketIsPluginActiveForNetwork extends TestCase {
	private $config;

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		update_site_option( 'active_sitewide_plugins', $this->config['active_sitewide_plugins'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCorrectState( $plugin, $expected ) {
		$this->assertSame( $this->config['active_sitewide_plugins'], get_site_option( 'active_sitewide_plugins' ) );
		$this->assertSame( $expected, rocket_is_plugin_active_for_network( $plugin ) );
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'rocketIsPluginActiveForNetwork' );
	}
}
