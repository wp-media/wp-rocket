<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 * @group AdminOnly
 */
class GetUserData extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'license.php';
	private static $client;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );

		$container = apply_filters( 'rocket_container', null );

		self::$client = $container->get( 'user_client' );
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
	}

	public function tearDown() {
		delete_transient( 'wp_rocket_customer_data' );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_customer_data', $expected );
		}

		if ( false === $expected ) {
			Functions\expect( 'wp_safe_remote_post' )
			->once()
			->with(
				UserClient::USER_ENDPOINT,
				[
					'body' => 'user_id=' . rawurlencode( self::getApiCredential( 'ROCKET_EMAIL' ) ) . '&consumer_key=' . self::getApiCredential( 'ROCKET_KEY' ),
				]
			)
			->andReturn( $config['response'] );
		}

		$this->assertEquals(
			$expected,
			self::$client->get_user_data()
		);

		if (
			false === $config['transient']
			&&
			false !== $expected
		) {
			$this->assertEquals(
				$expected,
				get_transient( 'wp_rocket_customer_data' )
			);
		}
	}

	public function set_consumer_email() {
		return self::getApiCredential( 'ROCKET_EMAIL' );
	}

	public function set_consumer_key() {
		return self::getApiCredential( 'ROCKET_KEY' );
	}
}
