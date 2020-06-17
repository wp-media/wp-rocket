<?php
/**
 * Unit tests for _rocket_get_directory_php_files_array().
 *
 * @package WP_Rocket\Tests\Unit\inc\functions
 * @author  Caspar Green
 * @since   ver 3.6.1
 */

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test _rocket_get_directory_php_files_array().
 *
 * @package WP_Rocket\Tests\Unit\inc\functions
 * @covers ::_rocket_get_config_path_php_files()
 * @author  Caspar Green
 * @since   ver 3.6.1
 */
class Test_RocketGetDirectoryPHPFilesArray extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetDirectoryPHPFilesArray.php';

	/**
	 * Set up before running these tests.
	 *
	 * @return void
	 * @since  ver 3.6.1
	 *
	 * @author Caspar Green
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/functions/files.php';
	}

	/**
	 * Set up before each test.
	 *
	 * @return void
	 * @since  ver 3.6.1
	 *
	 * @author Caspar Green
	 */
	public function setUp() {
		parent::setUp();

		$this->filesystem->delete( 'public/wp-content/wp-rocket-config/example.org.php' );
	}

	/**
	 * Test should return all php files in the directory.
	 *
	 * @dataProvider providerTestData
	 *
	 * @param array $contents       Files in the directory.
	 * @param array $expected_files Expected SplFileInfo objects.
	 *
	 * @return void
	 * @since        ver 3.6.1
	 *
	 * @author       Caspar Green
	 */
	public function testShouldReturnAllPHPFilesInDirectory( array $contents, array $expected_files ) {
		$expected_filenames = [];

		foreach ( $contents as $file ) {
			$this->filesystem->put_contents(
				'public/wp-content/wp-rocket-config/' . $file,
				'Some contents.'
			);
		}

		$files_array = _rocket_get_directory_php_files_array( 'vfs://public/wp-content/wp-rocket-config/' );

		foreach ( $files_array as $file ) {
			$expected_filenames[] = $file->getFilename();
		}

		$this->assertSame( $expected_files, $expected_filenames );
	}

	/**
	 * Test should return an empty array when the directory path is invalid.
	 *
	 * @return void
	 * @since  ver 3.6.1
	 *
	 * @author Caspar Green
	 */
	public function testShouldReturnEmptyArrayWhenDirectoryPathIsInvalid() {
		$this->assertSame(
			[],
			_rocket_get_directory_php_files_array( 'vfs://some/bogus/directory' )
		);
	}
}
