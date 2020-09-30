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
	public function tearDown() {
		delete_transient( 'wp_rocket_pricing' );

		parent::tearDown();
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
			->with( 'https://wp-rocket.me/stat/1.0/wp-rocket/pricing.php' )
			->andReturn( $config['response'] );
		}

		$this->assertEquals(
			$expected,
			$client->get_pricing_data()
		);

		if (
			false === $config['transient']
			&&
			false !== $expected
		) {
			$this->assertEquals(
				$expected,
				get_transient( 'wp_rocket_pricing' )
			);
		}
	}
}
