<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\PricingClient;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\PricingClient::get_pricing_data
 *
 * @group License
 */
class GetPricingData extends TestCase {
	protected static $transients = [
		'wp_rocket_pricing',
	];

	public function tearDown() {
		delete_transient( 'wp_rocket_pricing' );

		parent::tearDown();
	}

	public function setUp() {
		parent::setUp();

		delete_transient( 'wp_rocket_pricing' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new PricingClient();

		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_pricing', $expected );
		}

		if ( false === $expected ) {
			Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with( PricingClient::PRICING_ENDPOINT )
			->andReturn( $config['response'] );

			$this->assertFalse( $client->get_pricing_data() );
		} else {
			$this->assertEquals(
				array_keys( (array) $expected ),
				array_keys( (array) $client->get_pricing_data() )
			);

			if ( false === $config['transient'] ) {
				$this->assertEquals(
					array_keys( (array) $expected ),
					array_keys( (array) get_transient( 'wp_rocket_pricing' ) )
				);
			}
		}
	}
}
