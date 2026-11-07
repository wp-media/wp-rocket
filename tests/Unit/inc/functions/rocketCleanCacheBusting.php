<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_clean_cache_busting
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanCacheBusting extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanCacheBusting.php';
	private   $valid_extensions  = [ 'css', 'css.gz', 'js', 'js.gz' ];

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanBustingFiles( $extensions, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		foreach ( (array) $extensions as $ext ) {
			if ( ! in_array( $ext, $this->valid_extensions, true ) ) {
				continue;
			}
			Actions\expectDone( 'before_rocket_clean_busting' )->once()->with( $ext );
			Actions\expectDone( 'after_rocket_clean_cache_busting' )->once()->with( $ext );
		}

		// Run it.
		rocket_clean_cache_busting( $extensions );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}

	/**
	 * Busting files under a path with regex metacharacters (timestamped release folder) are cleaned.
	 */
	public function testShouldCleanBustingFilesWhenPathContainsRegexCharacters() {
		$this->wp_content_dir = 'vfs://public/releases/2024-09-02T11:32:23+0000/wp-content';
		$busting_path         = "{$this->wp_content_dir}/cache/busting/";

		mkdir( $busting_path . '1/wp-content/', 0777, true );
		$file = $busting_path . '1/wp-content/test.js';
		file_put_contents( $file, '' );

		Actions\expectDone( 'before_rocket_clean_busting' )->once()->with( 'js' );
		Actions\expectDone( 'after_rocket_clean_cache_busting' )->once()->with( 'js' );

		rocket_clean_cache_busting( 'js' );

		$this->assertFalse( file_exists( $file ) );
	}
}
