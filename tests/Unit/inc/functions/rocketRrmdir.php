<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketRrmdir extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketRrmdir.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete = $this->filesystem->getUrl( $to_delete );
		$this->generateEntriesShouldExistAfter( $expected['removed'] );

		// Check the action events.
		Actions\expectDone( 'before_rocket_rrmdir' )->times( $expected['before_rocket_rrmdir'] );
		Actions\expectDone( 'after_rocket_rrmdir' )->times( $expected['after_rocket_rrmdir'] );

		// Run it.
		rocket_rrmdir( $to_delete, $to_preserve );

		$dump_results = isset( $expected['dump_results'] );
		$this->checkEntriesDeleted( $expected['removed'] );
		$this->checkShouldNotDeleteEntries( $dump_results );
	}
}
