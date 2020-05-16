<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\ApiTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::preserve_authorization_token
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::preserve_authorization_token
 *
 * @group  RocketCDN
 * @group  AdminOnly
 * @group  RocketCDNAdminPage
 */
class Test_PreserveAuthorizationToken extends TestCase {
	use ApiTrait;

	private          $client;
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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldPreserveAuthorizationToken( $config, $expected, $sent = null ) {
		if ( isset( $config['getApiCredential'] ) ) {
			$expected['headers']['Authorization'] .= static::getApiCredential( 'ROCKETCDN_TOKEN' );
		}

		if ( is_null( $sent ) ) {
			$sent = $expected;
		}

		$args = apply_filters( 'http_request_args', $sent, $config['url'] );

		$this->assertSame( $expected, $args );
	}
}
