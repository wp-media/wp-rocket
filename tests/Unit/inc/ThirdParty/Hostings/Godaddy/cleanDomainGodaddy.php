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

class Test_cleanDomainGodaddy extends TestCase {

	public function testShouldDoBanRequest( ) {

		Functions\expect( 'home_url' )->andReturn( 'http://example.org' );
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


		Functions\expect( 'wp_cache_flush' )->once()->andReturn(null);

		Functions\expect('rocket_extract_url_component')
			->once()
			->with('http://example.org', PHP_URL_HOST)
			->andReturn( 'http://example.org' );

		Functions\expect( 'update_option' )->once()
			->with( 'gd_system_last_cache_flush', time() )->andReturn(null);

		Functions\expect( 'esc_url_raw' )->once()->with( 'http://vip-url.com/' )->andReturnFirstArg();

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://vip-url.com/',
				[
					'method'      => 'BAN',
					'blocking'    => false,
					'headers'     => [
						'Host' => 'http://example.org',
					],
				]
			);

		$godaddy = new Godaddy( 'http://vip-url.com/' );
		$godaddy->clean_domain_godaddy();
	}
}
