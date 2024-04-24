<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::_rocket_get_dir_files_by_regex()
 *
 * @group Functions
 */
class Test_RocketGetDirFilesByRegex extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/_rocketGetDirFilesByRegex.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAllPHPFilesInDirectory( $dir, $regex, array $expected ) {
		$actual = [];
		foreach ( _rocket_get_dir_files_by_regex( $dir, $regex ) as $file ) {
			$actual[] = $file->getFilename();
		}

		$this->assertSame( $expected, $actual );
	}
}
