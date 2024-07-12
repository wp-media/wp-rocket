<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::update_advanced_cache
 * @uses   ::rocket_get_filesystem_perms
 *
 * @group  AdvancedCache
 */
class Test_UpdateAdvancedCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/updateAdvancedCache.php';

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'rocket_generate_advanced_cache_file', [ $this, 'return_false' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testUpdateAdvancedCache( $set_filter, $expected ) {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		if ( $set_filter ) {
			add_filter( 'rocket_generate_advanced_cache_file', [ $this, 'return_false' ] );
		}

		$advanced_cache->update_advanced_cache();

		$this->assertSame( $expected, $this->filesystem->getFilesListing( 'vfs://public/wp-content/' ) );
	}

	/**
	 * @group Multisite
	 */
	public function testShouldNotUpdateWhenMultisiteAndSitesNotZero() {
		$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );

		$advanced_cache->update_advanced_cache( 1 );
	}

	/**
	 * @group Multisite
	 */
	public function testShouldUpdateWhenMultisiteAndSitesZero() {
		$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );

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
