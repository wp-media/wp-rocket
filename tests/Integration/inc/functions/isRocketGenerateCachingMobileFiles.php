<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::is_rocket_generate_caching_mobile_files
 * @uses   ::get_rocket_option
 *
 * @group  Options
 * @group  Functions
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
	public function testShouldReturnExpectedOptionValue( array $settings, $expected ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $this->config['settings'], $settings )
		);
		$this->assertSame( $expected, is_rocket_generate_caching_mobile_files() );
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
