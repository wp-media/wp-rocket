<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::_rocket_get_php_files_in_dir()
 *
 * @group Functions
 */
class Test_RocketGetDirectoryPHPFilesArray extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetPHPFilesInDir.php';

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/functions/files.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAllPHPFilesInDirectory( $dir, array $expected ) {
		$actual = [];
		foreach ( _rocket_get_php_files_in_dir( $dir ) as $file ) {
			$actual[] = $file->getFilename();
		}

		$this->assertSame( $expected, $actual );
	}
}
