<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\TestCredentials;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 * @group AdminOnly
 */
class Test_GetUserData extends TestCase {
	private static $client;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container = apply_filters( 'rocket_container', null );

		self::$client = $container->get( 'user_client' );
	}

	public function set_up() {
		parent::set_up();

		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
		delete_transient( UserClient::LICENCE_REJECTED_TRANSIENT );
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );

		// A customer-data transient write can cascade into a settings save (licence expiry disables RocketCDN):
		// keep its subscribers, which query tables this test doesn't install, out of the method under test.
		$this->unregisterAllCallbacks( 'update_option_wp_rocket_settings' );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
		delete_transient( UserClient::LICENCE_REJECTED_TRANSIENT );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected, $rejected = false ) {
		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_customer_data', $expected );
		}

		if ( ! empty( $config['flagged'] ) ) {
			set_transient( UserClient::LICENCE_REJECTED_TRANSIENT, 1, DAY_IN_SECONDS );
		}

		$this->config['http'] = [ UserClient::USER_ENDPOINT => $config['response'] ];

		$this->assertEquals(
			$expected,
			self::$client->get_user_data()
		);
		$this->assertSame( $rejected, (bool) get_transient( UserClient::LICENCE_REJECTED_TRANSIENT ) );
	}

	public function set_consumer_email() {
		return TestCredentials::EMAIL;
	}

	public function set_consumer_key() {
		return TestCredentials::KEY;
	}
}
