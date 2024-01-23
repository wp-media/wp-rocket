<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\DBTrait;
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
	use DBTrait;

	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpectedFiles( $urls, $config,  $expected ) {
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
