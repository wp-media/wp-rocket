<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\TestCredentials;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the licence rejection flag of \WP_Rocket\Engine\License\API\UserClient
 *
 * @covers \WP_Rocket\Engine\License\API\UserClient::get_user_data
 * @covers \WP_Rocket\Engine\License\API\UserClient::is_licence_rejected
 * @group  License
 */
class Test_LicenceErrorAnswer extends TestCase {
	private $client;
	private $transients;

	public function setUp(): void {
		parent::setUp();

		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( TestCredentials::KEY );
		$options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( TestCredentials::EMAIL );

		$this->client     = new UserClient( $options );
		$this->transients = [];

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'get_transient' )->alias(
			function ( $key ) {
				return $this->transients[ $key ] ?? false;
			}
		);
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value ) {
				$this->transients[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'delete_transient' )->alias(
			function ( $key ) {
				unset( $this->transients[ $key ] );
				return true;
			}
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function ( $response ) {
				return $response['response']['code'];
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function ( $response ) {
				return $response['body'];
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRecordTheLicenceRejection( $config, $expected ) {
		if ( ! empty( $config['flag'] ) ) {
			$this->transients[ UserClient::LICENCE_REJECTED_TRANSIENT ] = 1;
		}

		if ( ! empty( $config['backoff'] ) ) {
			$this->transients['wpr_user_information_timeout_active'] = true;
			Functions\expect( 'wp_safe_remote_post' )->never();
		} else {
			Functions\expect( 'wp_safe_remote_post' )->once()->andReturn( $config['response'] );
		}

		$this->assertEquals( $expected['data'], $this->client->get_user_data() );
		$this->assertSame( $expected['flag'], $this->client->is_licence_rejected() );
		$this->assertSame( $expected['backoff'], isset( $this->transients['wpr_user_information_timeout_active'] ) );
		$this->assertSame( false !== $expected['data'], isset( $this->transients['wp_rocket_customer_data'] ) );
	}
}
