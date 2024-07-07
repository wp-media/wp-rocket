<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::update_advanced_cache
 * @uses   ::rocket_get_filesystem_perms
 *
 * @group  AdvancedCache
 */
class Test_UpdateAdvancedCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/updateAdvancedCache.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testUpdateAdvancedCache( $set_filter, $expected ) {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );

		if ( $set_filter ) {
			Filters\expectApplied( 'rocket_generate_advanced_cache_file' )->andReturn( false );
			Functions\expect( 'rocket_get_filesystem_perms' )->never();
		} else {
			Filters\expectApplied( 'rocket_generate_advanced_cache_file' )->andReturn( true );
			Functions\expect( 'rocket_get_filesystem_perms' )
				->once()
				->andReturn( 420 );
		}

		$advanced_cache->update_advanced_cache();

		$this->assertSame( $expected, $this->filesystem->getFilesListing( 'vfs://public/wp-content/' ) );
	}

	/**
	 * @group Multisite
	 * @doesNotPerformAssertions
	 */
	public function testShouldNotUpdateWhenMultisiteAndSitesNotZero() {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );
		Functions\when( 'is_multisite' )->justReturn( true );

		$advanced_cache->update_advanced_cache( 1 );
	}

	/**
	 * @group Multisite
	 */
	public function testShouldUpdateWhenMultisiteAndSitesZero() {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );
		Functions\when( 'is_multisite' )->justReturn( true );

		$advanced_cache->update_advanced_cache();

		$this->assertSame(
			[
				'vfs://public/wp-content/cache/wp-rocket/index.html',
				'vfs://public/wp-content/advanced-cache.php',
			],
			$this->filesystem->getFilesListing( 'vfs://public/wp-content/' )
		);
	}
}
