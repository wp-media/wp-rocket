<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_cache_reject_cookies
 * @group Functions
 * @group Options
 */
class Test_GetRocketCacheRejectCookies extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldNameTheLoggedInCookie( $config, $expected ) {
		$this->constants['COOKIEHASH']       = $config['hash'];
		$this->constants['LOGGED_IN_COOKIE'] = $config['cookie'];

		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cache_reject_cookies', [] )
			->andReturn( $config['rejected'] );
		Functions\when( 'apply_filters' )->alias(
			function ( $name, $cookies ) use ( $config ) {
				return array_merge( $cookies, $config['filter'] ?? [] );
			}
		);

		$actual = ( $config['no_argument'] ?? false )
			? get_rocket_cache_reject_cookies()
			: get_rocket_cache_reject_cookies( $config['logged_in'] ?? true );

		$this->assertSame( $expected, $actual );
	}
}
