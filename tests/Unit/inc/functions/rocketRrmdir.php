<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketRrmdir extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketRrmdir.php';

	public function setUp() {
		parent::setUp();

		$this->setUpOriginalEntries();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete       = $this->filesystem->getUrl( untrailingslashit( $to_delete ) );
		$shouldNotRemove = empty( $expected['removed'] )
			? $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) )
			: $this->getNonCleaned( $expected['not_removed'] );

		// Check the action events.
		Actions\expectDone( 'before_rocket_rrmdir' )->times( $expected['before_rocket_rrmdir'] );
		Actions\expectDone( 'after_rocket_rrmdir' )->times( $expected['after_rocket_rrmdir'] );

		// Run it.
		rocket_rrmdir( $to_delete, $to_preserve );

		// Check the "removed" files and directories.
		foreach ( $expected['removed'] as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$this->assertFalse( $this->filesystem->exists( $dir ) );
			} else {
				$shouldNotRemove[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}

		// Check the "not-removed" files/directories still exist, i.e. were not deleted.
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$this->assertEmpty( array_diff( $entriesAfterCleaning, $shouldNotRemove ) );
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
