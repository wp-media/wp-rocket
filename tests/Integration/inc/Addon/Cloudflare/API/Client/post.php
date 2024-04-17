<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\API\Client;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\API\Client::post
 *
 * @group Cloudflare
 */
class TestPost extends TestCase {
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

		$auth = new APIKey( $config['email'], $config['api_key'] );
		$client = new Client( $auth );
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

	public function http_request() {
		return $this->response;
	}
}
