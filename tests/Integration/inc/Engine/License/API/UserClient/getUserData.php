<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 * @group AdminOnly
 */
class Test_GetUserData extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'license.php';
	private static $client;
	private $response;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );

		$container = apply_filters( 'rocket_container', null );

		self::$client = $container->get( 'user_client' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_customer_data' );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		remove_filter( 'pre_http_request', [ $this, 'set_response' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_customer_data', $expected );
		}

		$this->response = $config['response'];

		add_filter( 'pre_http_request', [ $this, 'set_response' ] );

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

	public function set_response() {
		return $this->response;
	}
}
