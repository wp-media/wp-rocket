<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_files
 * @uses  ::rocket_rrmdir
 * @uses  ::rocket_remove_url_protocol
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group thisone
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';
	private   $dirsToClean;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( WP_ROCKET_CACHE_PATH );

		$this->dirsToClean = [];
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $urls, $expected ) {
		$this->dirsToClean = $expected['cleaned'];

		$shouldNotClean = $this->getNonCleaned( $expected['non_cleaned'] );

		// Run it.
		rocket_clean_files( $urls );

		// Check the "cleaned" directories.
		foreach ( $expected['cleaned'] as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$this->assertFalse( $this->filesystem->exists( $dir ) );
			} else {
				$shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}

		// Check the non-cleaned files/directories still exist.
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$actual               = array_diff( $entriesAfterCleaning, $shouldNotClean );
		if ( ! empty( $expected['test_it'] ) ) {
			var_dump( $actual );
		} else {
			$this->assertEmpty( $actual );
		}
	}

	private function getNonCleaned( $config ) {
		$entries = [];
		foreach ( $config as $entry => $scanDir ) {
			$entries[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$entries = array_merge( $entries, $this->filesystem->getListing( $entry ) );
			}
		}

		return $entries;
	}
}
