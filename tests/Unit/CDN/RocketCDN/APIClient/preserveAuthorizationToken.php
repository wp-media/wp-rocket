<?php

namespace WP_Rocket\Tests\Unit\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\APIClient::preserve_authorization_token
 * @group RocketCDN
 */
class Test_PreserveAuthorizationToken extends TestCase {
	private $api_client;

	public function setUp() {
		parent::setUp();

		$this->api_client = new APIClient();
	}

	public function testShouldReturnSameArgsWhenURLNotRocketCDN() {
		$expected = [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		];

		$this->assertSame(
			$expected,
			$this->api_client->preserve_authorization_token( $expected, 'http://example.org' )
		);
	}

	public function testShouldReturnSameArgsWhenAuthorizationHeadersEmpty() {
		$expected = [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		];

		$this->assertSame(
			$expected,
			$this->api_client->preserve_authorization_token( $expected, 'https://rocketcdn.me/api/' )
		);
	}

	public function testShouldReturnSameArgsWhenAuthorizationHeadersCorrect() {
		Functions\when( 'get_option' )->justReturn( '1234' );

		$expected = [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token 1234'
			],
			'body'    => '',
		];

		$this->assertSame(
			$expected,
			$this->api_client->preserve_authorization_token( $expected, 'https://rocketcdn.me/api/' )
		);
	}

	public function testShouldReturnCorrectTokenWhenAuthorizationHeadersIncorrect() {
		Functions\when( 'get_option' )->justReturn( '1234' );

		$sent = [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ABCD'
			],
			'body'    => '',
		];

		$expected = [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token 1234'
			],
			'body'    => '',
		];

		$this->assertSame(
			$expected,
			$this->api_client->preserve_authorization_token( $sent, 'https://rocketcdn.me/api/' )
		);
	}
}
