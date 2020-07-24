<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Engine\Cache\WPCache;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::maybe_prevent_deactivation
 * @uses   ::find_wp_config_path
 *
 * @group  WPCache
 */
class Test_MaybePreventDeactivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/maybePreventDeactivation.php';

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMaybePreventDeactivation( $config, $expected ) {
		$wp_cache = new WPCache( $this->filesystem );

		if ( ! $config['file_exist'] ) {
			$this->filesystem->delete( 'vfs://public/wp-config.php' );
		}

		if ( isset( $config['set_filter_to_false'] ) && $config['set_filter_to_false'] ) {
			add_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false' ] );
		}

		$this->assertSame( $expected, $wp_cache->maybe_prevent_deactivation( [] ) );

	}
}
