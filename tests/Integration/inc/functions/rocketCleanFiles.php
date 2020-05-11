<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_files
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_dirs
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		// Clean out the cached dirs before we leave this test class.
		_rocket_get_cache_dirs( '', '', true );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['debug_fs'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpectedFiles( $urls, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		// Run it.
		rocket_clean_files( $urls );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
