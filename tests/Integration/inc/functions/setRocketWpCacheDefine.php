<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

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
	protected $config_file = '';

	public function tearDown()
	{
		remove_filter( 'rocket_wp_config_name', [ $this, 'setWpCacheFilePath' ] );
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheDefine( $config, $expected ) {
		$this->config_file     = $config['file'];
		$config_file_full_path = $this->config['vfs_dir'] . $this->config_file . '.php';

		add_filter( 'rocket_wp_config_name', [ $this, 'setWpCacheFilePath' ] );

		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		set_rocket_wp_cache_define( true );

		$actual = $this->filesystem->get_contents( $config_file_full_path );
		$this->assertEquals( $expected, str_replace( "\r\n", "\n", $actual ) );
	}

	public function setWpCacheFilePath($file) {
		return $this->config_file;
	}
}
