<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_advanced_cache_file
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_direct_filesystem
 *
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

		Functions\expect( 'rocket_get_constant' )
			->once()->with( 'WP_ROCKET_PHP_VERSION' )->andReturn( '5.6' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_INC_PATH' )->andReturn( 'vfs://public/wp-content/plugins/wp-rocket/inc/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_PATH' )->andReturn( 'vfs://public/wp-content/plugins/wp-rocket/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_CONFIG_PATH' )->andReturn( 'vfs://public/wp-content/wp-rocket-config/' )
			->andAlsoExpectIt()
			->once()->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );

		if ( $is_rocket_generate_caching_mobile_files ) {
			Functions\expect( 'rocket_get_constant' )
				->once()->with( 'WP_ROCKET_VENDORS_PATH' )->andReturn( 'vfs://public/wp-content/plugins/wp-rocket/inc/vendors/' );
		}

		$this->assertSame( $expected, get_rocket_advanced_cache_file() );
	}
}
