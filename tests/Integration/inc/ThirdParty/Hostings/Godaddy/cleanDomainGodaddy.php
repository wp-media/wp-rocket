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

		$host='example.org';
		$vip_url='vip-url.com';

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				esc_url_raw( $vip_url ),
				[
					'method'      => 'BAN',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		$godaddy = new Godaddy( $vip_url );
		$godaddy->clean_domain_godaddy();
	}
}
