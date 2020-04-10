<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
 * @uses  ::_rocket_preserve_directory
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketRrmdir extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketRrmdir.php';
	private $stats;

	public function setUp() {
		parent::setUp();

		$this->stats = [
			'before_rocket_rrmdir' => did_action( 'before_rocket_rrmdir' ),
			'after_rocket_rrmdir'  => did_action( 'after_rocket_rrmdir' ),
		];

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

		// Run it.
		rocket_rrmdir( $to_delete, $to_preserve );

		// Check the action events.
		$this->assertEquals(
			$expected['before_rocket_rrmdir'],
			did_action( 'before_rocket_rrmdir' ) - $this->stats['before_rocket_rrmdir']
		);
		$this->assertEquals(
			$expected['after_rocket_rrmdir'],
			did_action( 'after_rocket_rrmdir' ) - $this->stats['after_rocket_rrmdir']
		);

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
