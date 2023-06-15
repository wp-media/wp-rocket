<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\API\Client;

use Brain\Monkey\Functions;
use Exception;
use Mockery;
use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\API\Client::post
 *
 * @group Cloudflare
 */
class TestPost extends TestCase {
	protected $rocket_version = '3.13';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

		$auth   = Mockery::mock( AuthInterface::class );
		$client = new Client( $auth );

		$auth->expects()
			->is_valid_credentials()
			->andReturn( $config['valid_credentials'] );

		Functions\when( 'wp_json_encode' )
			->alias( function() use ( $config ) {
				return json_encode( $config['data'] );
			} );

		$auth->shouldReceive( 'get_headers' )
			->atMost()
			->once()
			->andReturn( [
				'X-Auth-Email' => 'roger@wp-rocket.me',
				'X-Auth-Key'   => '12345',
			] );

		Functions\when( 'wp_remote_request' )
			->justReturn( $config['response'] );

		Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( $config['valid_error'] )
			->andAlsoExpectIt()
			->atMost()
			->once()
			->andReturn( $config['request_error'] );

		if ( is_array( $config['response'] ) ) {
			Functions\when( 'wp_remote_retrieve_body' )
				->justReturn( $config['response']['body'] );
		}

		Functions\when( 'wp_sprintf_l' )
			->returnArg();

		$result = $client->post( $config['path'], $config['data'] );

		if ( 'error' === $expected['result'] ) {
			$this->assertInstanceOf( WP_Error::class, $result );

				$this->assertSame(
					$expected['error_code'],
					$result->get_error_code()
				);
		} else {
			$this->assertSame(
				$expected['result'],
				$result
			);
		}
	}
}
