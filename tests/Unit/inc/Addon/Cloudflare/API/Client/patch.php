<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\API\Client;

use Brain\Monkey\Functions;
use Exception;
use Mockery;
use WP_Rocket\Addon\Cloudflare\API\AuthenticationException;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Addon\Cloudflare\API\UnauthorizedException;
use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;
use WP_Rocket\Addon\Cloudflare\Auth\CredentialsException;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\API\Client::patch
 *
 * @group Cloudflare
 */
class TestPatch extends TestCase {
	protected $rocket_version = '3.13';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

		$auth   = Mockery::mock( AuthInterface::class );
		$client = new Client( $auth );

		if ( 'unauthenticated' === $expected ) {
			$this->expectException( AuthenticationException::class );
		}

		if ( 'credentials' === $expected ) {
			$auth->shouldReceive( 'is_valid_credentials' )
			->andThrow( CredentialsException::class );

			$this->expectException( CredentialsException::class );
		} else {
			$auth->expects()
			->is_valid_credentials()
			->andReturn( $config['valid_credentials'] );
		}

		if ( 'unauthorized' === $expected ) {
			$this->expectException( UnauthorizedException::class );
		}

		if ( 'exception' === $expected ) {
			$this->expectException( Exception::class );
		}

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

		Functions\when( 'is_wp_error' )
			->justReturn( $config['error'] );

		if ( is_array( $config['response'] ) ) {
			Functions\when( 'wp_remote_retrieve_body' )
				->justReturn( $config['response']['body'] );
		}

		Functions\when( 'wp_sprintf_l' )
			->returnArg();

		$this->assertSame(
			$expected,
			$client->patch( $config['path'], $config['data'] )
		);
	}
}
