<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_is_plugin_active_for_network
 * @group  Options
 * @group  Functions
 */
class Test_RocketIsPluginActiveForNetwork extends TestCase {
	private $config;

	protected function setUp() : void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCorrectState( $plugin, $expected ) {
		Functions\expect( 'is_multisite' )->once()->andReturn( true );
		Functions\expect( 'get_site_option' )
			->once()
			->with( 'active_sitewide_plugins' )
			->andReturn( $this->config['active_sitewide_plugins'] );

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
