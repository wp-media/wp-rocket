<?php

namespace WP_Rocket\Tests\Integration\inc\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_minify
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanMinify.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinified( $extensions, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Run it.
		rocket_clean_minify( $extensions );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
