<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Integration\TestCase;
use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::purge_cache_request
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
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
	 * Test should return the error packet when there's no subscription ID.
	 */
	public function testShouldReturnErrorPacketWhenNoSubscriptionId() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertSame(
			[
				'status'  => 'error',
				'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
			],
			( new APIClient )->purge_cache_request()
		);
	}

	/**
	 * Test should return the error packet when the subscription ID is 0.
	 */
	public function testShouldReturnErrorPacketWhenSubscriptionIdIsZero() {
		set_transient( 'rocketcdn_status', [ 'id' => 0, 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertSame(
			[
				'status'  => 'error',
				'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
			],
			( new APIClient )->purge_cache_request()
		);
	}

	/**
	 * Test should return error packet when no user token.
	 */
	public function testShouldReturnErrorPacketWhenNoToken() {
		set_transient( 'rocketcdn_status', [ 'id' => 1 ], MINUTE_IN_SECONDS );

		$this->assertSame(
			[
				'status'  => 'error',
				'message' => 'RocketCDN cache purge failed: Missing user token.',
			],
			( new APIClient )->purge_cache_request()
		);
	}

	/**
	 * Test should return error packet when subscription ID or token is invalid.
	 */
	public function testShouldReturnErrorPacketWhenInvalidSubscriptionIdOrToken() {
		set_transient( 'rocketcdn_status', [ 'id' => 1 ], MINUTE_IN_SECONDS );
		update_option( 'rocketcdn_user_token', '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b' );

		$this->assertSame(
			[
				'status'  => 'error',
				'message' => 'RocketCDN cache purge failed: The API returned an unexpected response code.',
			],
			( new APIClient )->purge_cache_request()
		);
	}

	/**
	 * Test should return the status when set in the transient.
	 */
	public function testShouldReturnSuccessPacketWhenAPIPurgedCache() {
		set_transient( 'rocketcdn_status', [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ], MINUTE_IN_SECONDS );
		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );

		$this->assertSame(
			[
				'status'  => 'success',
				'message' => 'RocketCDN cache purge successful.',
			],
			( new APIClient )->purge_cache_request()
		);
	}
}
