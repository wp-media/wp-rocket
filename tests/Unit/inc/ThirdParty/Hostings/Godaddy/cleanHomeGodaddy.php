<?php
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::godaddy_varnish_field
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

class Test_cleanHomeGodaddy extends TestCase {

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}

	public function testShouldPurgeHome( ) {

		$host_url='http://example.org';
		$vip_url='vip-url.com';
		$lang='';
		$GLOBALS['wp_rewrite'] = (object) [ 'pagination_base' => 'page' ];

		Functions\expect( 'get_rocket_i18n_home_url' )->once()->andReturn( $host_url );

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

		Functions\expect('rocket_extract_url_component')
			->once()
			->with($host_url.'/', PHP_URL_HOST)
			->andReturn( $host_url);

		Functions\expect('rocket_extract_url_component')
			->once()
			->with($host_url.'/page/', PHP_URL_HOST)
			->andReturn( $host_url);

		Functions\expect( 'wp_cache_flush' )->twice()->andReturn(null);


		Functions\expect( 'update_option' )->twice()
			->with( 'gd_system_last_cache_flush', time() )->andReturn(null);

		Functions\expect( 'esc_url_raw' )->once()->with( $vip_url.'/' )->andReturnFirstArg();
		Functions\expect( 'esc_url_raw' )->once()->with( $vip_url.'/page/' )->andReturnFirstArg();

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				$vip_url.'/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host_url,
					],
				]
			);

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				$vip_url.'/page/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host_url,
					],
				]
			);

		$godaddy = new Godaddy( $vip_url );
		$godaddy->clean_home_godaddy( $host_url,$lang );
	}
}
