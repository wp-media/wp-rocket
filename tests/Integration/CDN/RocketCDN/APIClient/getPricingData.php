<?php

namespace WP_Rocket\Tests\Integration\CDN\RocketCDN;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::get_pricing_data
 * @group  RocketCDN
 * @group  RocketCDNAPI
 */
class Test_GetPricingData extends TestCase {

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_pricing' );
	}

	/**
	 * Test should return the pricing when set in the transient.
	 */
	public function testShouldReturnPricingWhenInTransient() {
		$status = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];
		set_transient( 'rocketcdn_pricing', $status, MINUTE_IN_SECONDS );

		$this->assertSame( $status, ( new APIClient )->get_pricing_data() );
	}

	/**
	 * Test should return the pricing and set the transient when successfully received from the API.
	 */
	public function testShouldReturnPricingAndSetTransientWhenReceivedFromAPI() {
		$this->assertFalse( get_transient( 'rocketcdn_pricing' ) );

		// Run it.
		$actual = ( new APIClient )->get_pricing_data();

		$this->assertArrayHasKey( 'is_discount_active', $actual );
		$this->assertArrayHasKey( 'discounted_price_monthly', $actual );
		$this->assertArrayHasKey( 'discounted_price_yearly', $actual );
		$this->assertArrayHasKey( 'discount_campaign_name', $actual );
		$this->assertArrayHasKey( 'end_date', $actual );
		$this->assertArrayHasKey( 'monthly_price', $actual );
		$this->assertArrayHasKey( 'annual_price', $actual );
		$this->assertSame( $actual, get_transient( 'rocketcdn_pricing' ) );
	}
}
