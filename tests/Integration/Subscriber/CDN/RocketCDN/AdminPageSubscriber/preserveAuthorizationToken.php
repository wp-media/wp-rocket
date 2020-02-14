<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\Phpunit\Integration\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::preserve_authorization_token
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_PreserveAuthorizationToken extends TestCase {
	use \WPMedia\Phpunit\Integration\ApiTrait;

	private $client;
	protected static $api_credentials_config_file = 'rocketcdn.php';

	public function tearDown() {
		parent::tearDown();

		delete_option( 'rocketcdn_user_token' );
	}

	public function testShouldReturnSameArgsWhenURLNotRocketCDN() {
		$expected = [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		];

		$args = apply_filters( 'http_request_args', $expected, 'http://example.org' );

		$this->assertSame( $expected, $args );
	}

	public function testShouldReturnSameArgsWhenAuthorizationHeadersEmpty() {
		$expected = [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		];

		$args = apply_filters( 'http_request_args', $expected, 'https://rocketcdn.me/api/' );

		$this->assertSame( $expected, $args );
	}

	public function testShouldReturnSameArgsWhenAuthorizationHeadersCorrect() {
		$expected = [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' )
			],
			'body'    => '',
		];

		$args = apply_filters( 'http_request_args', $expected, 'https://rocketcdn.me/api/' );

		$this->assertSame( $expected, $args );
	}

	public function testShouldReturnCorrectTokenWhenAuthorizationHeadersIncorrect() {
		add_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );

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
				'Authorization' => 'token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' )
			],
			'body'    => '',
		];

		$args = apply_filters( 'http_request_args', $sent, 'https://rocketcdn.me/api/' );

		$this->assertSame( $expected, $args );
	}
}
