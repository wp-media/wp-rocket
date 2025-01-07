<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_is_plugin_active
 * @group  Options
 * @group  Functions
 */
class Test_RocketIsPluginActive extends TestCase {
	private $config;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function setUp() : void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		Functions\expect( 'get_option' )
			->once()
			->with( 'active_plugins', [] )
			->andReturn( $this->config['active_plugins'] );
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldReturnCorrectStateWhenNotMultisite( $plugin, $expected ) {
		Functions\when( 'rocket_is_plugin_active_for_network' )->justReturn( false );

		$this->assertSame( $expected, rocket_is_plugin_active( $plugin ) );
	}

	/**
	 * @dataProvider multisiteTestData
	 * @group        Multisite
	 */
	public function testShouldReturnCorrectStateWhenMultisite( $plugin, $expected, $is_multisite_active = false ) {
		Functions\when( 'rocket_is_plugin_active_for_network' )->justReturn( $is_multisite_active );

		$this->assertSame( $expected, rocket_is_plugin_active( $plugin ) );
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

	public function multisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['multisite'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'rocketIsPluginActive' );
	}
}
