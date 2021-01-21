<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\PricingClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\PricingClient::get_pricing_data
 *
 * @group License
 */
class GetPricingData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new PricingClient();

		Functions\expect( 'get_transient' )
			->once()
			->with( 'wp_rocket_pricing' )
			->andReturn( true === $config['transient'] ? $expected : false );

		if ( false !== $config['response'] ) {
			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with( PricingClient::PRICING_ENDPOINT )
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
			Functions\expect( 'wp_safe_remote_get' )->never();
		}

		if (
			false === $config['transient']
			&&
			false !== $expected
		) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'wp_rocket_pricing', Mockery::type( 'object' ), 12 * HOUR_IN_SECONDS );
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->assertEquals(
			$expected,
			$client->get_pricing_data()
		);
	}
}
