<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 */
class GetPricingData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new UserClient();

		if ( true === $config['transient'] ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'wp_rocket_customer_data' )
				->andReturn( $expected );
		} else {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'wp_rocket_customer_data' )
				->andReturn( false );
		}

		if ( false !== $config['response'] ) {
			Functions\expect( 'wp_safe_remote_post' )
				->once()
				->with( UserClient::USER_ENDPOINT )
				->andReturn( $config['response'] );

			if ( ! is_array( $config['response'] ) ) {
				Functions\when( 'wp_remote_retrieve_response_code' )
				->justReturn( '' );
			} elseif (
				is_array( $config['response'] )
				&&
				isset( $config['response']['code'] )
			) {
				Functions\when( 'wp_remote_retrieve_response_code' )
				->justReturn( $config['response']['code'] );
			}

			if (
				is_array( $config['response'] )
				&&
				isset( $config['response']['body'] )
			) {
				Functions\when( 'wp_remote_retrieve_body' )
					->justReturn( $config['response']['body'] );
			}
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
			$client->get_user_data()
		);
	}
}
