<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\DataManager::save_cpcss
 *
 * @group CriticalPath
 * @group vfs
 */
class Test_SaveCPCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/saveCPCSS.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $url, $path, $cpcss_code, $is_mobile, $expected, $expected_cpcss ) {
		$cache_path = $this->filesystem->getUrl( $this->config['vfs_dir'] );
		$file_path  = "{$cache_path}1/{$path}";

		$data_manager = new DataManager( $cache_path, $this->filesystem );
		$actual       = $data_manager->save_cpcss( $path, $cpcss_code, $url, $is_mobile );

		$this->assertSame( $expected, $actual );
		$this->assertTrue( $this->filesystem->exists( $file_path ) );
		$this->assertSame( $expected_cpcss, $this->filesystem->get_contents( $file_path ) );
	}
}
