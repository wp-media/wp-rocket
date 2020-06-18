<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::set_wp_cache_define
 * @uses   ::rocket_valid_key
 *
 * @group WPCache
 * @group vfs
 */
class Test_SetWpCacheDefine extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/setWpCacheDefine.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheDefine( $config, $expected ) {
		$config_file_full_path = $this->config['vfs_dir'] . $config['file'] . '.php';

		Functions\when( 'rocket_find_wpconfig_path' )->justReturn( $config_file_full_path );
		Functions\expect( 'rocket_valid_key' )->once()->andReturn( $config['valid_key'] );

		$wp_cache = new WPCache( $this->filesystem );

		$wp_cache->set_wp_cache_define( true );

		$actual = $this->filesystem->get_contents( $config_file_full_path );
		$this->assertEquals( $expected, str_replace( "\r\n", "\n", $actual ) );
	}
}
