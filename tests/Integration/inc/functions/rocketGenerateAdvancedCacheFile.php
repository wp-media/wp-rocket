<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_generate_advanced_cache_file
 * @uses   ::get_rocket_advanced_cache_file
 * @uses   ::rocket_put_content
 * @uses   ::rocket_get_constant
 *
 * @group  AdvancedCache
 * @group  Functions
 * @group  Files
 */
class Test_RocketGenerateAdvancedCacheFile extends FilesystemTestCase {
	protected      $path_to_test_data   = '/inc/functions/rocketGenerateAdvancedCacheFile.php';
	private        $advanced_cache_file = 'vfs://public/wp-content/advanced-cache.php';
	private static $original_settings   = [];
	private        $old_settings        = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		// Restore the original settings before exiting.
		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	public function setUp() {
		parent::setUp();

		$this->old_settings = array_merge( self::$original_settings, $this->config['settings'] );
		update_option( 'wp_rocket_settings', $this->old_settings );
	}

	public function tearDown() {
		parent::tearDown();

		delete_option( 'wp_rocket_settings' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateAdvancedCacheFile( $settings, $expected_content, $when_file_not_exist = false ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->old_settings, $settings )
		);

		if ( $when_file_not_exist ) {
			$this->filesystem->delete( $this->advanced_cache_file );
		}

		// Run it.
		rocket_generate_advanced_cache_file();

		$this->assertTrue( $this->filesystem->exists( $this->advanced_cache_file ) );

		// Check that the file was generated with the expected content.
		$actual_content = $this->filesystem->get_contents( $this->advanced_cache_file );
		$this->assertSame( $expected_content, $actual_content );
	}
}
