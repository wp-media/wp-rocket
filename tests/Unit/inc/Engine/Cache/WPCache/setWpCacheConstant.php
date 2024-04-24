<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::set_wp_cache_constant
 * @uses   ::rocket_valid_key
 *
 * @group WPCache
 * @group vfs
 */
class Test_SetWpCacheConstant extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/setWpCacheConstant.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheConstant( $config, $expected ) {
		$wp_config = $this->filesystem->getUrl( 'wp-config.php' );
		$this->filesystem->put_contents( $wp_config, $config['original'] );

		Functions\expect( 'rocket_valid_key' )->once()->andReturn( $config['valid_key'] );
		Functions\when( 'current_user_can' )->justReturn( true );
		Filters\expectApplied( 'rocket_set_wp_cache_constant' )
			->atMost()
			->times( 1 )
			->with( true )
			->andReturn( $config['filter'] );

		$wp_cache = new WPCache( $this->filesystem );

		$wp_cache->set_wp_cache_constant( true );

		$this->assertEquals(
			$expected,
			str_replace( "\r\n", "\n", $this->filesystem->get_contents( $wp_config ) )
		);
	}
}
