<?php

namespace WP_Rocket\Tests\Integration\CDN\RocketCDN\APIClient;

use WPMedia\Phpunit\Integration\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\APIClient::update_website_status
 * @group  RocketCDN
 * @group  RocketCDNAPI
 */
class Test_UpdateWebsiteStatus extends TestCase {
	use \WPMedia\Phpunit\Integration\ApiTrait;

	private $client;
	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() {
		$this->client = new APIClient();
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
	}

	public function testShouldReturnNullWhenNoSubscriptionId() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertNull( $this->client->update_website_status( true ) );
	}

	public function testShouldReturnNullWhenInvalidSubscriptionId() {
		set_transient( 'rocketcdn_status', [ 'id' => 0, 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertNull( $this->client->update_website_status( true ) );
	}

	public function testShouldReturnNullWhenNoUserToken() {
		set_transient( 'rocketcdn_status', [ 'id' => 1 ], MINUTE_IN_SECONDS );

		$this->assertNull( $this->client->update_website_status( true ) );
	}

	public function testShouldSendPatchRequest() {
		set_transient( 'rocketcdn_status', [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ], MINUTE_IN_SECONDS );
		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );

		$this->client->update_website_status( true );

		$response = wp_remote_get(
			'https://rocketcdn.me/api/website/search/?url=http://example.org',
			[
				'headers' => [
					'Authorization' => 'Token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' ),
				],
			]
		);

		$website_data = json_decode( wp_remote_retrieve_body( $response ) );

		$this->assertSame( true, $website_data->is_active );
	}
}
