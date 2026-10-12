<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 * @group AdminOnly
 */
class Test_GetUserData extends TestCase {
	use ApiTrait;
	use HttpRequestTrait;

	protected static $api_credentials_config_file = 'license.php';
	private static $client;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );

		$container = apply_filters( 'rocket_container', null );

		self::$client = $container->get( 'user_client' );
	}

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_customer_data', $expected );
		}

		$this->config['http'] = [ UserClient::USER_ENDPOINT => $config['response'] ];

		$this->assertEquals(
			$expected,
			self::$client->get_user_data()
		);
	}

	public function set_consumer_email() {
		return self::getApiCredential( 'ROCKET_EMAIL' );
	}

	public function set_consumer_key() {
		return self::getApiCredential( 'ROCKET_KEY' );
	}
}
