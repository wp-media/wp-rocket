<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
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
	public function testShouldCleanExpectedFiles( $urls, $expected ) {
		$this->dirsToClean    = $expected['cleaned'];
		$this->getShouldNotCleanEntries( $expected['non_cleaned'] );

		// Run it.
		rocket_clean_files( $urls );

		$this->checkCleanedIsDeleted( $expected['cleaned'] );
		$this->checkNonCleanedExist();
	}
}
