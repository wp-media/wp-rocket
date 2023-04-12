<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\API\Client;

use Exception;
use WP_Rocket\Addon\Cloudflare\API\AuthenticationException;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Addon\Cloudflare\API\UnauthorizedException;
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Auth\CredentialsException;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\API\Client::patch
 *
 * @group Cloudflare
 */
class TestPatch extends TestCase {
	protected $response;
	protected $rocket_version = '3.13';

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'http_request'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->response = $config['response'];
		$email = 'roger@wp-rocket.me';
		$api_key = '12345';

		add_filter( 'pre_http_request', [ $this, 'http_request'] );

		if ( 'unauthenticated' === $expected ) {
			$email = 'rogerwp-rocket.me';
			$api_key = '12345';
			$this->expectException( AuthenticationException::class );
		}

		if ( 'credentials' === $expected ) {
			$email = '';
			$api_key = '';
			$this->expectException( CredentialsException::class );
		}

		if ( 'unauthorized' === $expected ) {
			$this->expectException( UnauthorizedException::class );
		}

		if ( 'exception' === $expected ) {
			$this->expectException( Exception::class );
		}

		$auth = new APIKey( $email, $api_key );
		$client = new Client( $auth );

		$this->assertSame(
			$expected,
			$client->patch( $config['path'], $config['data'] )
		);
	}

	public function http_request() {
		return $this->response;
	}
}
