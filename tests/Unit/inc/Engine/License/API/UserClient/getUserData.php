<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 */
class GetPricingData extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'license.php';

	private $client;
	private $options;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() {
		$this->options = Mockery::mock( Options_Data::class );
		$this->client  = new UserClient( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'wp_rocket_customer_data' )
			->andReturn( true === $config['transient'] ? $expected : false );

		if ( false !== $config['response'] ) {
			$this->options->shouldReceive( 'get' )
			->twice()
			->with( 'consumer_key', '' )
			->andReturn( self::getApiCredential( 'ROCKET_KEY' ) );

			$this->options->shouldReceive( 'get' )
				->twice()
				->with( 'consumer_email', '' )
				->andReturn( self::getApiCredential( 'ROCKET_EMAIL' ) );

			Functions\when( 'sanitize_key' )->returnArg();

			Functions\expect( 'wp_safe_remote_post' )
				->once()
				->with(
					UserClient::USER_ENDPOINT,
					[
						'body' => 'user_id=' . rawurlencode( self::getApiCredential( 'ROCKET_EMAIL' ) ) . '&consumer_key=' . self::getApiCredential( 'ROCKET_KEY' ),
					]
				)
				->andReturn( $config['response'] );

			Functions\when( 'wp_remote_retrieve_response_code' )
			->justReturn(
				is_array( $config['response'] ) && isset( $config['response']['code'] )
				? $config['response']['code']
				: ''
			);

			Functions\when( 'wp_remote_retrieve_body' )
			->justReturn(
				is_array( $config['response'] ) && isset( $config['response']['body'] )
				? $config['response']['body']
				: ''
			);
		} else {
			Functions\expect( 'wp_safe_remote_post' )->never();
		}

		if (
			false === $config['transient']
			&&
			false !== $expected
		) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'wp_rocket_customer_data', Mockery::type( 'object' ), DAY_IN_SECONDS );
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->assertEquals(
			$expected,
			$this->client->get_user_data()
		);
	}
}
