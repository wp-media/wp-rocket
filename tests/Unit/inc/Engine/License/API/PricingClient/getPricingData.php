<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\PricingClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\PricingClient::get_pricing_data
 *
 * @group  License
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
			->andReturn( true === $config['pricing-transient'] ? $expected['result'] : false );

		Functions\when( 'wp_remote_retrieve_response_code' )
			->justReturn(
				is_array( $config['response'] )
					? $config['response']['response']['code']
					: ''
			);

		Functions\when( 'wp_remote_retrieve_body' )
			->justReturn(
				is_array( $config['response'] )
					? $config['response']['body']
					: ''
			);

		if ( false === $config['pricing-transient'] ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'wp_rocket_pricing_timeout_active' )
				->andReturn( $config['timeout-active'] );
		}

		if ( false !== $config['response'] ) {
			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with( PricingClient::PRICING_ENDPOINT )
				->andReturn( $config['response'] );

			if (
				! is_array( $config['response'] ) ||
				200 !== $config['response']['response']['code'] ||
				empty( $config['response']['body'] )
			) {
				Functions\expect( 'get_transient' )
					->once()
					->with( 'wp_rocket_pricing_timeout' )
					->andReturn( (int) $config['timeout-duration'] );

				$duration = $config['timeout-duration']
					? min( [
						2 * $config['timeout-duration'],
						rocket_get_constant( 'DAY_IN_SECONDS' )
					] )
					: 300;

				Functions\expect( 'set_transient' )
					->with(
						'wp_rocket_pricing_timeout',
						$duration,
						rocket_get_constant( 'WEEK_IN_SECONDS' ) )
					->andAlsoExpectIt()
					->with( 'wp_rocket_pricing_timeout_active', true, $duration );
			} else {
				Functions\expect( 'set_transient' )
					->once()
					->with( 'wp_rocket_pricing', Mockery::type( 'object' ), 12 * HOUR_IN_SECONDS );
				Functions\expect( 'delete_transient' )->twice();
			}
		}

		$this->assertEquals( $expected['result'], $client->get_pricing_data() );
	}
}
