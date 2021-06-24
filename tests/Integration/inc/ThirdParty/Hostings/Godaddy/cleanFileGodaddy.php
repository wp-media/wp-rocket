<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_file_godaddy
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;
use Brain\Monkey\Filters;

class Test_cleanFileGodaddy extends TestCase {

	public function testShouldPurgeFile( ) {

/*		$host='example.org';
		$vip_url='vip-url.com';

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				esc_url_raw( $vip_url ),
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'headers'     => [
						'Host' => $host,
					],
				]
			);

		$godaddy = new Godaddy( $vip_url );
		$godaddy->clean_file_godaddy( '' );*/

		Filters\expectApplied('pre_http_request')
			->once()
			/*->with(false,[])*/
			->andReturn('response');

		do_action( 'before_rocket_clean_file', home_url() );


		//add_filters( 'pre_http_request', false, $parsed_args, $url );
	}
}
