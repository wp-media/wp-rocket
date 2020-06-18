<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::_rocket_get_php_files_in_dir()
 *
 * @group Functions
 */
class Test_RocketGetDirectoryPHPFilesArray extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetPHPFilesInDir.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/functions/files.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAllPHPFilesInDirectory(array $expected_files ) {
		$expected_filenames = [];
		var_dump($this->filesystem->getFilesListing('vfs://public/wp-content/wp-rocket-config/' ) );

		$files_array = _rocket_get_php_files_in_dir( 'vfs://public/wp-content/wp-rocket-config/' );
//var_dump($files_array);
		foreach ( $files_array as $file ) {
			$expected_filenames[] = $file->getFilename();
		}

		$this->assertSame( $expected_files, $expected_filenames );
	}

	public function testShouldReturnEmptyArrayWhenDirectoryPathIsInvalid() {
		$this->assertSame(
			[],
			_rocket_get_php_files_in_dir( 'vfs://some/bogus/directory' )
		);
	}
}
