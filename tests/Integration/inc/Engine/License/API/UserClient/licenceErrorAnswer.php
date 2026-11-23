<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\TestCredentials;

/**
 * Test class covering the licence rejection flag of \WP_Rocket\Engine\License\API\UserClient::get_user_data
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

	/**
	 * @dataProvider providerRejections
	 */
	public function testShouldFlagTheRejectionAndKeepTheBackoff( $code, $body ) {
		$this->config['http'] = [ UserClient::USER_ENDPOINT => $this->response( $code, $body ) ];

		$this->assertFalse( self::$client->get_user_data() );
		$this->assertFalse( get_transient( 'wp_rocket_customer_data' ) );
		$this->assertNotFalse( get_transient( 'wpr_user_information_timeout_active' ) );
		$this->assertTrue( self::$client->is_licence_rejected() );
		$this->assertFalse( ( new User( false ) )->can_use_ai() );
	}

	public function providerRejections() {
		return [
			'edge 403 page'   => [ 403, '<html><head><title>403 Forbidden</title></head><body><center><h1>403 Forbidden</h1></center><hr><center>nginx</center></body></html>' ],
			'JSON 404 answer' => [ 404, '{"code":404,"detail":"User not found.","success":false}' ],
		];
	}

	public function testShouldClearTheFlagOnAValidAnswer() {
		set_transient( UserClient::LICENCE_REJECTED_TRANSIENT, 1, DAY_IN_SECONDS );
		$this->config['http'] = [ UserClient::USER_ENDPOINT => $this->response( 200, '{"ID":"1","status":"active"}' ) ];

		$this->assertIsObject( self::$client->get_user_data() );
		$this->assertFalse( self::$client->is_licence_rejected() );
		$this->assertTrue( ( new User( false ) )->can_use_ai() );
	}

	private function response( int $code, string $body ): array {
		return [
			'headers'  => [],
			'body'     => $body,
			'response' => [ 'code' => $code ],
			'cookies'  => [],
			'filename' => '',
		];
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
		delete_transient( UserClient::LICENCE_REJECTED_TRANSIENT );
	}
}
