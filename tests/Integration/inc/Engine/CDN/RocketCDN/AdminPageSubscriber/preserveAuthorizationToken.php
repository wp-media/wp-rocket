<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\ApiTrait;
use  WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::preserve_authorization_token
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::preserve_authorization_token
 *
 * @group  AdminOnly
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_PreserveAuthorizationToken extends TestCase {
	use ApiTrait;

	private          $client;
	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function set_up() {
		add_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );
	}

	public function tear_down() {
		delete_option( 'rocketcdn_user_token' );

		parent::tear_down();
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
