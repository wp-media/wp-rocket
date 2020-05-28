<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::purge_cache_request
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  RocketCDN
 * @group  RocketCDNAPI
 */
class Test_PurgeCacheRequest extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $transient, $option, $expected, $success = false ) {
		if ( $success ) {
			$transient = [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ];
			$option    = self::getApiCredential( 'ROCKETCDN_TOKEN' );
		}
		set_transient( 'rocketcdn_status', $transient, MINUTE_IN_SECONDS );
		if ( ! empty( $option ) ) {
			update_option( 'rocketcdn_user_token', $option );
		}

		$this->assertSame(
			$expected,
			( new APIClient )->purge_cache_request()
		);
	}
}
