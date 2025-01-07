<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 *
 * @group  AdvancedCache
 */
class Test_GetAdvancedCacheContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/getAdvancedCacheContent.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedContent( $settings, $expected, $is_rocket_generate_caching_mobile_files ) {
		Functions\expect( 'is_rocket_generate_caching_mobile_files' )
			->once()
			->andReturn( $is_rocket_generate_caching_mobile_files );

		Filters\expectApplied( 'rocket_advanced_cache_file' )
			->once()
			->with( $expected )
			->andReturnFirstArg();

		// Run it.
		$advanced_cache = new AdvancedCache(
			$this->filesystem->getUrl( $this->config['vfs_dir'] ),
			$this->filesystem
		);

		$this->assertSame( $expected, $advanced_cache->get_advanced_cache_content() );
	}
}
