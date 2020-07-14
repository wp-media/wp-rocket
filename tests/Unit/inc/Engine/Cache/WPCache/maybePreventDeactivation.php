<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::maybe_prevent_deactivation
 * @uses   ::find_wp_config_path
 *
 * @group  WPCache
 */
class Test_MaybePreventDeactivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/maybePreventDeactivation.php';

	public function testShouldBailOutWhenConfigFileFound() {
		$wp_cache = new WPCache( $this->filesystem );

		$this->assertSame( [], $wp_cache->maybe_prevent_deactivation( [] ) );
	}

	public function testShouldRBailOutWhenSetCacheConstFilterTrue() {
		$this->filesystem->delete('vfs://public/wp-config.php' );
		$wp_cache = new WPCache( $this->filesystem );

		Filters\expectApplied( 'rocket_set_wp_cache_constant' )->andReturn( false );

		$this->assertSame( [], $wp_cache->maybe_prevent_deactivation( [] ) );
	}

	public function testShouldAddCauseToCausesWhenNotPrevented() {
		$this->filesystem->delete('vfs://public/wp-config.php' );
		$wp_cache = new WPCache( $this->filesystem );

		Filters\expectApplied( 'rocket_set_wp_cache_constant' )->andReturn( true );

		$this->assertSame( [ 'wpconfig' ], $wp_cache->maybe_prevent_deactivation( [] ) );
	}

}
