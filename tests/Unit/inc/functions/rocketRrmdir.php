<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
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
		$to_delete = $this->filesystem->getUrl( untrailingslashit( $to_delete ) );

		// Check the action events.
		Actions\expectDone( 'before_rocket_rrmdir' )->times( $expected['before_rocket_rrmdir'] );
		Actions\expectDone( 'after_rocket_rrmdir' )->times( $expected['after_rocket_rrmdir'] );

		// Run it.
		rocket_rrmdir( $to_delete, $to_preserve );

		// Check the "deleted" files/directories no longer exist, i.e. were deleted.
		foreach ( $expected['deleted'] as $entry ) {
			$this->assertFalse( $this->filesystem->exists( $entry ) );
		}

		// Check the non-deleted files/directories still exist, i.e. were not deleted.
		$should_exist = array_diff( $this->original_entries, $expected['deleted'] );
		foreach ( $should_exist as $entry ) {
			$this->assertTrue( $this->filesystem->exists( $entry ) );
		}
	}
}
