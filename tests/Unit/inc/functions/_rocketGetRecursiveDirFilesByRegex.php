<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::_rocket_get_cache_dirs
 *
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test__RocketGetRecursiveDirFilesByRegex extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/_rocketGetRecursiveDirFilesByRegex.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetFiles( $config, $expected ) {
		$regex = isset( $config['regex'] ) ? $config['regex'] : '';

		$actual = [];
		$actual_files = _rocket_get_recursive_dir_files_by_regex( $regex );
		foreach ( $actual_files as $item ) {
			$actual[] = $item->getPathName();
		}
		$this->assertEquals($expected, $actual);

	}

}
