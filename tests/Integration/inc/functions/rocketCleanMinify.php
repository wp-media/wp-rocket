<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_minify
 * @group Functions
 * @group Files
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected static $path_to_test_data = '/inc/functions/rocketCleanMinify.php';
	private static $origin_files;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		static::$origin_files = array_map(
			function ( $file ) {
				return "cache/min/1/{$file}";
			},
			array_keys( static::$config['structure']['cache']['min']['1'] )
		);
	}

	public function tearDown() {
		delete_option( 'wp_rocket_settings' );

		parent::tearDown();
	}

	public function testPath() {
		$this->assertSame( 'vfs://wp-content/cache/min/', WP_ROCKET_MINIFY_CACHE_PATH );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldCleanMinified( $config, $filesToClean ) {
		$cache = $this->scandir( 'cache/min/1/' );

		// Check files before cleaning.
		$this->assertSame( static::$origin_files, $cache );

		rocket_clean_minify( $config );

		foreach ( $cache as $file ) {
			// Check that the files were cleaned.
			if ( in_array( $file, $filesToClean, true ) ) {
				$this->assertFalse( $this->filesystem->exists( $file ) );
			} else {
				$this->assertTrue( $this->filesystem->exists( $file ) );
			}
		}
	}
}
