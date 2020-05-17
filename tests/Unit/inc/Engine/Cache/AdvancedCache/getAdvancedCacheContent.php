<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content
 *
 * @uses   ::rocket_get_constant
 *
 * @group  AdvancedCache
 */
class Test_GetAdvancedCacheContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/getAdvancedCacheContent.php';
	private $advanced_cache;

	public function setUp() {
		parent::setUp();

		$this->advanced_cache = new AdvancedCache( $this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/cache/' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedContent( $settings, $expected, $is_rocket_generate_caching_mobile_files ) {
		Functions\expect( 'is_rocket_generate_caching_mobile_files' )
			->once()
			->andReturn( $is_rocket_generate_caching_mobile_files );

		$this->assertSame( $expected, $this->advanced_cache->get_advanced_cache_content() );
	}
}
