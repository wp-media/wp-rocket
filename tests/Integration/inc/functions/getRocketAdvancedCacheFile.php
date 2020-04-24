<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_advanced_cache_file
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Files
 */
class Test_IsRocketGenerateCachingMobileFiles extends TestCase {
	private $config;
	private $original_settings;

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public function tearDown() {
		parent::tearDown();

		if ( empty( $this->original_settings ) ) {
			delete_option( 'wp_rocket_settings' );
		} else {
			update_option( 'wp_rocket_settings', $this->original_settings );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedOptionValue( $settings, $expected ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $this->config['settings'], $settings )
		);

		Functions\expect( 'rocket_get_constant' )
			->once()->with( 'WP_ROCKET_PHP_VERSION' )->andReturn( '5.6' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_INC_PATH' )->andReturn( WP_ROCKET_PLUGIN_ROOT . '/inc/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_PATH' )->andReturn( 'vfs://public/wp-content/plugins/wp-rocket/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_CONFIG_PATH' )->andReturn( 'vfs://public/wp-content/wp-rocket-config/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );

		$this->assertSame( $expected, get_rocket_advanced_cache_file() );
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
