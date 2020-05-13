<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::get_rocket_advanced_cache_file
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdvancedCache
 * @group  Functions
 * @group  Files
 */
class Test_GetRocketAdvancedCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/getRocketAdvancedCacheFile.php';
	private   $original_settings;

	public function setUp() {
		parent::setUp();

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
	public function testShouldReturnExpectedContent( $settings, $expected ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $this->config['settings'], $settings )
		);

		$this->assertSame( $expected, get_rocket_advanced_cache_file() );
	}
}
