<?php

namespace WP_Rocket\Tests\Integration\inc\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_minify
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanMinify.php';

	public function tearDown() {
		delete_option( 'wp_rocket_settings' );

		parent::tearDown();
	}

	public function testPath() {
		$this->assertSame( 'vfs://public/wp-content/cache/min/', WP_ROCKET_MINIFY_CACHE_PATH );
	}

	public function testShouldFireEventsForEachExt() {
		rocket_clean_minify( [ 'css' ] );

		$expected = 1;
		$this->assertEquals( $expected, did_action( 'before_rocket_clean_minify' ) );
		$this->assertEquals( $expected, did_action( 'after_rocket_clean_minify' ) );

		rocket_clean_minify( [ 'css', 'js' ] );

		$expected += 2;
		$this->assertEquals( $expected, did_action( 'before_rocket_clean_minify' ) );
		$this->assertEquals( $expected, did_action( 'after_rocket_clean_minify' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinified( $config, $filesToClean ) {
		$cache = $this->stripRoot( $this->filesystem->getFilesListing( 'wp-content/cache/min' ) );

		// Check files before cleaning.
		$this->assertSame( $this->original_files, $cache );

		rocket_clean_minify( $config );

		$after_cache = $this->stripRoot( $this->filesystem->getFilesListing( 'wp-content/cache/min' ) );

		// Check the "cleaned" files were deleted.
		$this->assertEquals( $filesToClean, array_intersect( $filesToClean, $cache ) );
		$this->assertEquals( $filesToClean, array_diff( $filesToClean, $after_cache ) );
		$this->assertNotContains( $filesToClean, $after_cache );

		// Check that non-cleaned files still exists, i.e. were not deleted.
		$this->assertEquals( $after_cache, array_intersect( $after_cache, $cache ) );
	}

	private function stripRoot( $files ) {
		return array_map(
			function( $file ) {
				return str_replace( 'vfs://public/', '', $file );
			},
			$files
		);
	}
}
