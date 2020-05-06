<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_disable_cdn
 * @group  RocketCDN
 */
class Test_MaybeDisableCDN extends TestCase {

	public function testShouldReturnNullWhenSubscriptionRunning() {
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocketcdn_check_subscription_status_event' )
			->andReturn( false );
		Functions\expect( 'wp_schedule_single_event' )
			->once();

		$api = Mockery::mock('\WP_Rocket\Engine\CDN\RocketCDN\APIClient');
		$api->shouldReceive( 'get_subscription_data' )
		    ->andReturn( [
			    'subscription_status'           => 'running',
			    'subscription_next_date_update' => time(),
		    ] );

		$cdn_options_manager = Mockery::mock('\WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager');

		$data_manager = new DataManagerSubscriber(
			$api,
			$cdn_options_manager
		);

		$this->assertNull( $data_manager->maybe_disable_cdn() );
	}

	public function testShouldDisableCDNWhenSubscriptionCancelled() {
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );

		$api = Mockery::mock('\WP_Rocket\Engine\CDN\RocketCDN\APIClient');
		$api->shouldReceive( 'get_subscription_data' )
			->andReturn( [
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => time(),
			] );

		$cdn_options_manager = Mockery::mock('\WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager');
		$cdn_options_manager->shouldReceive( 'disable' )
			->once();

		$data_manager = new DataManagerSubscriber(
			$api,
			$cdn_options_manager
		);

		$data_manager->maybe_disable_cdn();
	}
}
