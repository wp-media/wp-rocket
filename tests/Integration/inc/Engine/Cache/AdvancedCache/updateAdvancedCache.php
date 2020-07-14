<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Cache\AdvancedCache::update_advanced_cache
 * @uses   ::rocket_get_filesystem_perms
 *
 * @group  AdvancedCache
 */
class Test_UpdateAdvancedCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/updateAdvancedCache.php';

	public function testShouldBailOutWhenShortCircuitFilterReturnsFalse() {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		add_filter( 'rocket_generate_advanced_cache_file', '__return_false' );

		$advanced_cache->update_advanced_cache();

		$this->assertSame(
			[ 'vfs://public/wp-content/cache/wp-rocket/index.html' ],
			$this->filesystem->getFilesListing( 'vfs://public/wp-content/' )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldWriteAdvancedCache( $expected ) {
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		$advanced_cache->update_advanced_cache();

		$this->assertSame(
			$expected,
			$this->filesystem->getFilesListing( 'vfs://public/wp-content/' )
		);
	}
}
