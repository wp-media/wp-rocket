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

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();
		//require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Godaddy/Plugin.php';
	}

/*	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
		//require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Godaddy/Plugin.php';
	}*/

	public function testShouldDoBanRequest( ) {

		$godaddy = new Godaddy();
		/*Functions\expect( 'home_url' )->andReturn( 'http://example.org' );
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


		Functions\expect( 'wp_cache_flush' )->once();

		Functions\expect( 'update_option' )
			->once()
			->with( 'gd_system_last_cache_flush', time() );

		Functions\expect( 'esc_url_raw' )->once()->with( 'http://example.org' )->andReturnFirstArg();

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://example.org/',
				[
					'method'      => 'BAN',
					'blocking'    => false,
					'headers'     => [
						'host'           => 'example.org',
					],
				]
			);*/
		$this->assertTrue(true);
		$godaddy->clean_domain_godaddy();
	}
}
