<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::get_rocket_advanced_cache_file
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdvancedCache
 * @group  Functions
 * @group  Files
 */
class Test_GetRocketAdvancedCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/getRocketAdvancedCacheFile.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedContent( $settings, $expected, $is_rocket_generate_caching_mobile_files ) {
		Functions\expect( 'is_rocket_generate_caching_mobile_files' )
			->once()
			->andReturn( $is_rocket_generate_caching_mobile_files );

		$this->assertSame( $expected, get_rocket_advanced_cache_file() );
	}
}
