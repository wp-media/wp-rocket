<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::maybe_set_wp_cache
 *
 * @group WPCache
 * @group vfs
 */
class Test_MaybeSetWpCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/maybeSetWpCache.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMaybeAddWpCacheConstant( $config, $expected ) {
		$wp_config = $this->filesystem->getUrl( 'wp-config.php' );
		$this->filesystem->put_contents( $wp_config, $config['original'] );

		$this->constants['DOING_AJAX'] = $config['doing_ajax'];
		$this->constants['DOING_AUTOSAVE'] = $config['doing_autosave'];

		Functions\expect( 'rocket_valid_key' )
			->atMost()
			->times( 1 )
			->andReturn( $config['valid_key'] );
		Functions\when( 'current_user_can' )->justReturn( true );
		Filters\expectApplied( 'rocket_set_wp_cache_constant' )
			->atMost()
			->times( 1 )
			->with( true )
			->andReturn( $config['filter'] );

		$wp_cache = new WPCache( $this->filesystem );

		$wp_cache->maybe_set_wp_cache();

		$this->assertEquals(
			$expected,
			str_replace( "\r\n", "\n", $this->filesystem->get_contents( $wp_config ) )
		);
	}
}
