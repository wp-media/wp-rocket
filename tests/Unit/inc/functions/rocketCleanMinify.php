<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_clean_minify
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanMinify.php';
	private   $valid_extensions  = [ 'css', 'css.gz', 'js', 'js.gz' ];

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinified( $extensions, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		foreach ( (array) $extensions as $ext ) {
			if ( ! in_array( $ext, $this->valid_extensions, true ) ) {
				continue;
			}
			Actions\expectDone( 'before_rocket_clean_minify' )->once()->with( $ext );
			Actions\expectDone( 'after_rocket_clean_minify' )->once()->with( $ext );
		}

		// Run it.
		rocket_clean_minify( $extensions );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}

	/**
	 * Minified files under a path with regex metacharacters (timestamped release folder) are cleaned.
	 */
	public function testShouldCleanMinifiedWhenPathContainsRegexCharacters() {
		$this->wp_content_dir = 'vfs://public/releases/2024-09-02T11:32:23+0000/wp-content';
		$min_path             = "{$this->wp_content_dir}/cache/min/";

		mkdir( $min_path . '1/', 0777, true );
		$file = $min_path . '1/5c795b0e3a1884eec34a989485f863ff.js';
		file_put_contents( $file, '' );

		Actions\expectDone( 'before_rocket_clean_minify' )->once()->with( 'js' );
		Actions\expectDone( 'after_rocket_clean_minify' )->once()->with( 'js' );

		rocket_clean_minify( 'js' );

		$this->assertFalse( file_exists( $file ) );
	}
}
