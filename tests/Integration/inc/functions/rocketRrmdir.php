<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

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

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete = $this->filesystem->getUrl( $to_delete );
		$this->generateEntriesShouldExistAfter( $expected['removed'] );

		// Run it.
		rocket_rrmdir( $to_delete, $to_preserve );

		$dump_results = isset( $expected['dump_ results'] );
		$this->checkEntriesDeleted( $expected['removed'], $dump_results );
		$this->checkShouldNotDeleteEntries( $dump_results );
	}
}
