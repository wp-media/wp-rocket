<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\ApiTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::preserve_authorization_token
 *
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::preserve_authorization_token
 *
 * @group  RocketCDN
 * @group  AdminOnly
 * @group  RocketCDNAdminPage
 */
class Test_PreserveAuthorizationToken extends TestCase {
	use ApiTrait;

	private $client;
	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() {
		add_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );
	}

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

	public function testShouldReturnSameArgsWhenAuthorizationHeadersEmptyAndEndpointIsPricing() {
		$expected = [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		];

		$args = apply_filters( 'http_request_args', $expected, 'https://rocketcdn.me/api/pricing' );

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

	public function testShouldReturnCorrectTokenWhenAuthorizationHeadersEmpty() {
		$sent = [
			'method'  => 'GET',
			'headers' => [],
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

	public function testShouldReturnCorrectTokenWhenAuthorizationHeadersIncorrect() {
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
