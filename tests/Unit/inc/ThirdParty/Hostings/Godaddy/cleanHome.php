<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\GoDaddy;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_home
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
class Test_cleanHome extends TestCase {
	public function tearDown(): void {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}

	public function testShouldPurgeHome( ) {
		$url      = 'http://example.org';
		$host     = 'example.org';
		$vip_url  = 'vip-url.com';
		$full_url = 'http://' . $vip_url;
		$lang     = '';
		$GLOBALS['wp_rewrite'] = (object) [ 'pagination_base' => 'page' ];

		Functions\expect( 'get_rocket_i18n_home_url' )->once()->andReturn( $url );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
				return parse_url( $url, $component );
			} );

		Functions\when( 'set_url_scheme')->alias( function ( $url ) {
			$url = trim( $url );
			if ( substr( $url, 0, 2 ) === '//' ) {
				$url = 'http:' . $url;
			}

			return preg_replace( '#^\w+://#', 'http://', $url );
		});

		Functions\expect( 'wp_cache_flush' )->twice();


		Functions\expect( 'update_option' )->twice()
			->with( 'gd_system_last_cache_flush', time() );

		Functions\expect( 'esc_url_raw' )->once()->with( $full_url )->andReturnFirstArg();
		Functions\expect( 'esc_url_raw' )->once()->with( $full_url . '/page' )->andReturnFirstArg();

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				$full_url,
				[
					'method'      => 'BAN',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				$full_url .'/page',
				[
					'method'      => 'BAN',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		$godaddy = new Godaddy( $vip_url );
		$godaddy->clean_home( $url, $lang );
	}
}
