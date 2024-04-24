<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use Brain\Monkey\Functions;
use WP_Error;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\DataManager::delete_cpcss
 * @uses ::rocket_direct_filesystem
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_DeleteCPCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/deleteCPCSS.php';

	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$cache_path = $this->filesystem->getUrl( $this->config['vfs_dir'] );
		$file       = "{$cache_path}1/{$config['path']}";
		$is_mobile  = isset( $config['is_mobile'] ) ? $config['is_mobile'] : false;

		// Check if the file exists before starting.
		if ( $expected['deleted'] ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		if ( isset( $config['change_permissions'] ) && $config['change_permissions'] ) {
			$this->changePermissions( $file );
		}

		// Run it.
		$data_manager = $this->setUpMocks( $cache_path );
		$actual       = $data_manager->delete_cpcss( $config['path'], $is_mobile );

		if ( $expected['deleted'] ) {
			// Assert success.
			$this->assertSame( $expected['deleted'], $actual );
			$this->assertFalse( $this->filesystem->exists( $file ) );

		} else {
			// Assert WP_Error.
			$this->assertInstanceOf( WP_Error::class, $actual );
			$this->assertSame( $expected['code'], $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'], $actual->get_error_data() );
		}
	}

	private function setUpMocks( $cpcss_cache_path ) {
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		return new DataManager( $cpcss_cache_path, $this->filesystem );
	}
}
