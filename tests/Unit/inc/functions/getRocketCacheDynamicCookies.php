<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_cache_dynamic_cookies
 *
 * @group Functions
 * @group Options
 */
class Test_GetRocketCacheDynamicCookies extends TestCase {
	/**
	 * Checks the list the plugin builds the cache file name from.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   What the filter returns.
	 * @param array $expected What the function returns for it.
	 *
	 * @return void
	 */
	public function testShouldGetRocketCacheDynamicCookies( $config, $expected ) {
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'rocket_cache_dynamic_cookies', [] )
			->andReturn( $config['filter'] );

		$this->assertSame( $expected, get_rocket_cache_dynamic_cookies() );
	}
}
