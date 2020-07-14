<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

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

		Filters\expectApplied( 'rocket_generate_advanced_cache_file' )->andReturn( false );
		Functions\expect( 'rocket_get_filesystem_perms' )->never();

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

		Filters\expectApplied( 'rocket_generate_advanced_cache_file' )->andReturn( true );
		Functions\expect( 'rocket_get_filesystem_perms' )
			->once()
			->andReturn( 420 );

		$advanced_cache->update_advanced_cache();

		$this->assertSame(
			$expected,
			$this->filesystem->getFilesListing( 'vfs://public/wp-content/' )
		);
	}
}
