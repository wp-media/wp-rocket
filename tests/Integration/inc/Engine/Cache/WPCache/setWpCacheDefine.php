<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::set_wp_cache_define
 * @uses   ::rocket_valid_key
 *
 * @group WPCache
 * @group vfs
 */
class Test_SetWpCacheDefine extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/setWpCacheDefine.php';
	protected $config_file = '';
	private static $wp_cache;

	public static function setUpBeforeClass() {
		$container = apply_filters( 'rocket_container', null );

		self::$wp_cache = $container->get( 'wp_cache' );
	}

	public function tearDown() {
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

		self::$wp_cache->set_wp_cache_define( true );

		$actual = $this->filesystem->get_contents( $config_file_full_path );
		$this->assertEquals( $expected, str_replace( "\r\n", "\n", $actual ) );
	}

	public function setWpCacheFilePath( $file ) {
		return $this->config_file;
	}
}
