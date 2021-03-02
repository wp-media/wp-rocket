<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\PricingClient;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\PricingClient::get_pricing_data
 *
 * @group  License
 */
class GetPricingData extends TestCase {
	protected static $transients = [
		'wp_rocket_pricing',
		'wp_rocket_pricing_timeout',
		'wp_rocket_pricing_timeout_active'
	];

	public function setUp() {
		parent::setUp();

		delete_transient( 'wp_rocket_pricing' );
		delete_transient( 'wp_rocket_pricing_timeout' );
		delete_transient( 'wp_rocket_pricing_timeout_active' );
	}

	public function tearDown() {
		delete_transient( 'wp_rocket_pricing' );
		delete_transient( 'wp_rocket_pricing_timeout' );
		delete_transient( 'wp_rocket_pricing_timeout_active' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new PricingClient();

		if ( true === $config['pricing-transient'] ) {
			set_transient( 'wp_rocket_pricing', $expected['result'] );
		}

		if ( false !== $config['timeout-duration'] ) {
			set_transient( 'wp_rocket_pricing_timeout', $config['timeout-duration'], WEEK_IN_SECONDS );
		}

		if ( true === $config['timeout-active'] ) {
			set_transient( 'wp_rocket_pricing_timeout_active', true, WEEK_IN_SECONDS );
		}

		if ( false === $config['timeout-active'] && false === $expected['result'] ) {
			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with( PricingClient::PRICING_ENDPOINT )
				->andReturn( $config['response'] );

			$this->assertFalse( $client->get_pricing_data() );
		} else {
			$this->assertEquals(
				array_keys( (array) $expected['result'] ),
				array_keys( (array) $client->get_pricing_data() )
			);

			if ( false === $config['pricing-transient'] ) {
				$this->assertEquals(
					array_keys( (array) $expected['result'] ),
					array_keys( (array) get_transient( 'wp_rocket_pricing' ) )
				);
			}
		}

		if ( false !== $config['timeout-duration'] ) {
			$this->assertEquals(
				$expected['timeout-duration'],
				get_transient( 'wp_rocket_pricing_timeout' )
			);
		}
	}
}
