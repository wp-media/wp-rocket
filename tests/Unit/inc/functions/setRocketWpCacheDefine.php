<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::set_rocket_wp_cache_define
 * @uses   ::rocket_valid_key
 * @uses   ::rocket_find_wpconfig_path
 * @uses   ::rocket_direct_filesystem
 * @uses   ::rocket_put_content
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_SetRocketWpCacheDefine extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/setRocketWpCacheDefine.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheDefine( $config, $expected ) {
		$config_file_full_path = $this->config['vfs_dir'] . $config['file'] . '.php';

		Functions\when( 'rocket_find_wpconfig_path' )->justReturn( $config_file_full_path );
		Functions\expect( 'rocket_valid_key' )->once()->andReturn( $config['valid_key'] );

		set_rocket_wp_cache_define( true );

		$actual = $this->filesystem->get_contents( $config_file_full_path );
		$this->assertEquals( $expected, str_replace( "\r\n", "\n", $actual ) );
	}
}
