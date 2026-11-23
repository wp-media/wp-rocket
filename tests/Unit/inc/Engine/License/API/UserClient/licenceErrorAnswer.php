<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\TestCredentials;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the licence error answers of \WP_Rocket\Engine\License\API\UserClient
 *
 * @group License
 */
class LicenceErrorAnswer extends TestCase {
	private const ERROR_BODY = '{"code":404,"detail":"User not found.","success":false}';

	private $client;
	private $transients;
	private $deleted;

	public function setUp(): void {
		parent::setUp();

		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( TestCredentials::KEY );
		$options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( TestCredentials::EMAIL );

		$this->client     = new UserClient( $options );
		$this->transients = [];
		$this->deleted    = [];

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value, $expiration ) {
				$this->transients[ $key ] = [ $value, $expiration ];
				return true;
			}
		);
		Functions\when( 'delete_transient' )->alias(
			function ( $key ) {
				$this->deleted[] = $key;
				return true;
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function ( $response ) {
				return $response['body'];
			}
		);
	}

	private function answer( int $code, string $body ): void {
		Functions\when( 'wp_safe_remote_post' )->justReturn(
			[
				'headers'  => [],
				'body'     => $body,
				'response' => [ 'code' => $code ],
				'cookies'  => [],
				'filename' => '',
			]
		);
	}

	/**
	 * @dataProvider providerResponseCodes
	 */
	public function testValidResponseCode( $code, $expected ) {
		$method = new ReflectionMethod( UserClient::class, 'valid_response_code' );
		$method->setAccessible( true );

		$this->assertSame( $expected, $method->invoke( $this->client, [ 'response' => [ 'code' => $code ] ] ) );
	}

	public function providerResponseCodes() {
		return [
			'200'        => [ 200, true ],
			'202'        => [ 202, true ],
			'403'        => [ 403, true ],
			'404'        => [ 404, true ],
			'500'        => [ 500, false ],
			'301'        => [ 301, false ],
			'empty code' => [ '', true ],
		];
	}

	public function testShouldReturnTheErrorAnswerAndCacheItBriefly() {
		$this->answer( 404, self::ERROR_BODY );

		$data = $this->client->get_user_data();

		$this->assertEquals( json_decode( self::ERROR_BODY ), $data );
		$this->assertSame( 5 * MINUTE_IN_SECONDS, $this->transients['wp_rocket_customer_data'][1] );
		$this->assertArrayNotHasKey( 'wpr_user_information_timeout', $this->transients );
		$this->assertArrayNotHasKey( 'wpr_user_information_timeout_active', $this->transients );
		$this->assertContains( 'wpr_user_information_timeout_active', $this->deleted );
	}

	public function testShouldReturnTheErrorAnswerOf403() {
		$this->answer( 403, '{"code":403,"detail":"You do not have permission.","success":false}' );

		$data = $this->client->get_user_data();

		$this->assertFalse( $data->success );
		$this->assertArrayNotHasKey( 'wpr_user_information_timeout_active', $this->transients );
	}

	public function testErrorAnswerMeansNoAssistant() {
		$this->answer( 404, self::ERROR_BODY );

		$this->assertFalse( ( new User( $this->client->get_user_data() ) )->can_use_ai() );
	}

	public function testShouldCacheASuccessfulAnswerForADay() {
		$this->answer( 200, '{"ID":"1","status":"active"}' );

		$this->client->get_user_data();

		$this->assertSame( DAY_IN_SECONDS, $this->transients['wp_rocket_customer_data'][1] );
	}

	/**
	 * @dataProvider providerFailures
	 */
	public function testShouldStayFalseAndArmTheBackoff( $code, $body ) {
		$this->answer( $code, $body );

		$this->assertFalse( $this->client->get_user_data() );
		$this->assertArrayNotHasKey( 'wp_rocket_customer_data', $this->transients );
		$this->assertArrayHasKey( 'wpr_user_information_timeout_active', $this->transients );
	}

	public function providerFailures() {
		return [
			'404 not JSON'             => [ 404, 'error 404' ],
			'404 JSON without success' => [ 404, '{"detail":"x"}' ],
			'404 empty body'           => [ 404, '' ],
			'500 with an error body'   => [ 500, self::ERROR_BODY ],
			'301'                      => [ 301, self::ERROR_BODY ],
		];
	}
}
