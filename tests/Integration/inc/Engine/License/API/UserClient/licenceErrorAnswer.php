<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\TestCredentials;

/**
 * Test class covering the licence error answers of \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 * @group AdminOnly
 */
class Test_LicenceErrorAnswer extends TestCase {
	private static $client;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container = apply_filters( 'rocket_container', null );

		self::$client = $container->get( 'user_client' );
	}

	public function set_up() {
		parent::set_up();

		$this->delete_transients();
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
	}

	public function tear_down() {
		$this->delete_transients();
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );

		parent::tear_down();
	}

	public function testShouldReturnTheErrorAnswerWithoutArmingTheBackoff() {
		$body                 = '{"code":404,"detail":"User not found.","success":false}';
		$this->config['http'] = [
			UserClient::USER_ENDPOINT => [
				'headers'  => [],
				'body'     => $body,
				'response' => [ 'code' => 404 ],
				'cookies'  => [],
				'filename' => '',
			],
		];

		$data = self::$client->get_user_data();

		$this->assertEquals( json_decode( $body ), $data );
		$this->assertFalse( get_transient( 'wpr_user_information_timeout_active' ) );
		$this->assertFalse( get_transient( 'wpr_user_information_timeout' ) );
		$this->assertEquals( json_decode( $body ), get_transient( 'wp_rocket_customer_data' ) );
		$this->assertFalse( ( new User( $data ) )->can_use_ai() );
	}

	public function set_consumer_email() {
		return TestCredentials::EMAIL;
	}

	public function set_consumer_key() {
		return TestCredentials::KEY;
	}

	private function delete_transients() {
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
	}
}
