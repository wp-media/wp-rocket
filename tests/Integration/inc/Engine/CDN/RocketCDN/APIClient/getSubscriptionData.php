<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @group  RocketCDN
 * @group  RocketCDNAPI
 */
class Test_GetSubscriptionData extends TestCase {
	use ApiTrait;

	private $client;
	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() {
		parent::setUp();

		$this->client = new APIClient();
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
	}

	/**
	 * Test should return the status when set in the transient.
	 */
	public function testShouldReturnStatusWhenInTransient() {
		$status = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];
		set_transient( 'rocketcdn_status', $status, MINUTE_IN_SECONDS );

		$this->assertSame( $status, $this->client->get_subscription_data() );
	}

	/**
	 * Test should return the default status when there's no user token saved in options.
	 */
	public function testShouldReturnDefaultWhenNoUserToken() {
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );

		$expected = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];
		$this->assertSame( $expected, $this->client->get_subscription_data() );
	}

	/**
	 * Test should return the status when set in the transient.
	 */
	public function testShouldReturnDefaultAndSetTransientWhenInvalidUserToken() {
		// Check that API sends us a 401.
		$response = wp_remote_get(
			APIClient::ROCKETCDN_API . 'website/search/?url=' . home_url(),
			[
				'headers' => [
					'Authorization' => 'Token 9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
				],
			]
		);
		$this->assertEquals( 401, wp_remote_retrieve_response_code( $response ) );

		// Now run the code and test.
		update_option( 'rocketcdn_user_token', '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b' );
		$expected = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
		$this->assertSame( $expected, $this->client->get_subscription_data() );
		$this->assertSame( $expected, get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Test should return the defaults and set the transient when the user's token is valid but no data was received
	 * from the API.
	 */
	public function testShouldReturnDefaultAndSetTransientWhenValidUserTokenButNoDataReceived() {
		// Check that API sends us a 200.
		$response = wp_remote_get(
			APIClient::ROCKETCDN_API . 'website/search/?url=' . home_url(),
			[
				'headers' => [
					'Authorization' => 'Token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' ),
				],
			]
		);
		$this->assertEquals( 200, wp_remote_retrieve_response_code( $response ) );

		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );
		$expected = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];

		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
		$this->assertSame( $expected, $this->client->get_subscription_data() );
		$this->assertSame( $expected, get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Test should return the data from the API and set the transient when the user's token is valid and data is
	 * received from the API.
	 */
	public function testShouldReturnDataAndSetTransientWhenReceivedFromAPI() {
		// Check that API sends us a 200.
		$response = wp_remote_get(
			APIClient::ROCKETCDN_API . 'website/search/?url=' . home_url(),
			[
				'headers' => [
					'Authorization' => 'Token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' ),
				],
			]
		);
		$this->assertEquals( 200, wp_remote_retrieve_response_code( $response ) );

		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );

		$expected_keys = [
			'id',
			'is_active',
			'cdn_url',
			'subscription_next_date_update',
			'subscription_status',
		];

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );

		$data = $this->client->get_subscription_data();

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $data );
		}

		$this->assertSame( $data['id'], (int) self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) );
		$this->assertTrue( in_array( $data['is_active'], [ true, false ], true ) );
		$this->assertSame( $data['cdn_url'], self::getApiCredential( 'ROCKETCDN_URL' ) );
		$this->assertTrue( in_array( $data['subscription_status'], [ 'running', 'cancelled' ], true ) );
		$this->assertSame( $data, get_transient( 'rocketcdn_status' ) );
	}
}
