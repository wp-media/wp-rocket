<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

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

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'wp_strip_all_tags' )->andReturnFirstArg();
		Functions\expect( 'rocket_put_content' )
			->once()
			->with( $cache_path . '1/' . $path, $expected_cpcss )
			->andReturn( $expected );

		$data_manager = new DataManager( $cache_path, $this->filesystem );
		$actual       = $data_manager->save_cpcss( $path, $cpcss_code, $url, $is_mobile );

		$this->assertSame( $expected, $actual );
	}
}
