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

		$host='example.org';
		$vip_url='vip-url.com';
		$lang='';

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				esc_url_raw( $vip_url ).'/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				esc_url_raw( $vip_url ).'/page/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		$godaddy = new Godaddy( $vip_url );
		$godaddy->clean_home_godaddy( '',$lang );
	}
}
