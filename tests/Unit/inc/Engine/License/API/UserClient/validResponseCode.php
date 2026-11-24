<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::valid_response_code
 *
 * @covers \WP_Rocket\Engine\License\API\UserClient::valid_response_code
 * @group  License
 */
class Test_ValidResponseCode extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$transients = [];

		if ( $config['flagged'] ) {
			$transients[ UserClient::LICENCE_REJECTED_TRANSIENT ] = 1;
		}

		Functions\when( 'set_transient' )->alias(
			function ( $key, $value ) use ( &$transients ) {
				$transients[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'delete_transient' )->alias(
			function ( $key ) use ( &$transients ) {
				unset( $transients[ $key ] );
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

		$method = new ReflectionMethod( UserClient::class, 'valid_response_code' );
		$method->setAccessible( true );

		$valid = $method->invoke(
			new UserClient( Mockery::mock( Options_Data::class ) ),
			[
				'body'     => $config['body'],
				'response' => [ 'code' => $config['code'] ],
			]
		);

		$this->assertSame( $expected['valid'], $valid );
		$this->assertSame( $expected['flagged'], isset( $transients[ UserClient::LICENCE_REJECTED_TRANSIENT ] ) );
	}
}
