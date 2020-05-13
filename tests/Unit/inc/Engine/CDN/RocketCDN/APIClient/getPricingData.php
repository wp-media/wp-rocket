<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;
use Mockery;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_pricing_data
 * @group RocketCDN
 */
class Test_GetPricingData extends TestCase {
	private $pricing_data = [
		'is_discount_active'       => true,
		'discounted_price_monthly' => 5.99,
		'discounted_price_yearly'  => 59.0,
		'discount_campaign_name'   => 'Launch',
		'end_date'                 => '2020-01-30',
		'monthly_price'            => 7.99,
		'annual_price'             => 79.0,
	];

	public function testShouldReturnCachedArrayWhenDataInTransient() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocketcdn_pricing' )
			->andReturn( $this->pricing_data );

		$client = new APIClient();
		$this->assertSame( $this->pricing_data, $client->get_pricing_data() );
	}

	public function testShouldReturnWPErrorWhenResponseNot200() {
		$this->mockCommonWpFunctions();

		Functions\when( 'get_transient' )->justReturn( false );

		Functions\expect( 'wp_remote_get' )
			->once()
			->with( 'https://rocketcdn.me/api/pricing' )
			->andReturn( false );

		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 400 );

		$wp_error = Mockery::mock( WP_Error::class );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'RocketCDN is not available at the moment. Plese retry later' );

		$client = new APIClient();
		$this->assertInstanceOf( WP_Error::class, $client->get_pricing_data() );
	}

	public function testShouldReturnWPErrorWhenReponseDataIsEmpty() {
		$this->mockCommonWpFunctions();

		Functions\when( 'get_transient' )->justReturn( false );

		Functions\expect( 'wp_remote_get' )
			->once()
			->with( 'https://rocketcdn.me/api/pricing' )
			->andReturn( false );

		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );

		$wp_error = Mockery::mock( WP_Error::class );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'RocketCDN is not available at the moment. Plese retry later' );

		$client = new APIClient();
		$this->assertInstanceOf( WP_Error::class, $client->get_pricing_data() );
	}

	public function testShouldReturnPricingArrayWhenSuccessful() {
		$this->mockCommonWpFunctions();

		Functions\when( 'get_transient' )->justReturn( false );

		Functions\expect( 'wp_remote_get' )
			->once()
			->with( 'https://rocketcdn.me/api/pricing' )
			->andReturn( false );

		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{
            "is_discount_active": true,
            "discounted_price_monthly": 5.99,
            "discounted_price_yearly": 59.0,
            "discount_campaign_name": "Launch",
            "end_date": "2020-01-30",
            "monthly_price": 7.99,
            "annual_price": 79.0
        }' );
		Functions\when( 'set_transient' )->justReturn( false );

		$client = new APIClient();
		$this->assertSame( $this->pricing_data, $client->get_pricing_data() );
	}
}
