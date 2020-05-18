<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Error;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::delete_cpcss
 * @uses   ::rocket_direct_filesystem
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_DeleteCPCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/deleteCPCSS.php';

	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$cache_path = $this->filesystem->getUrl( $this->config['vfs_dir'] );
		$file       = "{$cache_path}1/{$config['path']}";

		// Check if the file exists before starting.
		if ( $expected['deleted'] ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		if ( isset( $config['change_permissions'] ) && $config['change_permissions'] ) {
			$this->changePermissions( $cache_path );
		}

		// Run it.
		$data_manager = new DataManager( $cache_path, $this->filesystem );
		$actual       = $data_manager->delete_cpcss( $config['path'] );

		if ( isset( $expected['deleted'] ) && true === $expected['deleted'] ) {
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

	private function changePermissions( $cache_path ) {
		$dir = $this->filesystem->getDir( "{$cache_path}1/posts/" );
		$dir->chmod( 0000 ); // Only the root user.
	}
}
