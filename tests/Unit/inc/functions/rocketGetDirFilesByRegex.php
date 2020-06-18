<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_get_dir_files_by_regex()
 *
 * @group Functions
 */
class Test_RocketGetDirFilesByRegex extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetDirFilesByRegex.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/functions/files.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAllPHPFilesInDirectory( $dir, $regex, array $expected ) {
		$actual = [];
		foreach ( rocket_get_dir_files_by_regex( $dir, $regex ) as $file ) {
			$actual[] = $file->getFilename();
		}

		$this->assertSame( $expected, $actual );
	}
}
