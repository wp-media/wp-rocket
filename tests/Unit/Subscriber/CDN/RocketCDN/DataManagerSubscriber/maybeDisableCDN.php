<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::maybe_disable_cdn
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

		$api = $this->createMock( \WP_Rocket\CDN\RocketCDN\APIClient::class );
		$api->expects( $this->once() )
			->method( 'get_subscription_data' )
			->willReturn( [
				'subscription_status' => 'running',
				'subscription_next_date_update' => time(),
			] );

		$data_manager = new DataManagerSubscriber(
			$api,
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$this->assertNull( $data_manager->maybe_disable_cdn() );
	}

	public function testShouldDisableCDNWhenSubscriptionCancelled() {
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );

		$api = $this->createMock( \WP_Rocket\CDN\RocketCDN\APIClient::class );
		$api->expects( $this->once() )
			->method( 'get_subscription_data' )
			->willReturn( [
				'subscription_status' => 'cancelled',
				'subscription_next_date_update' => time(),
			] );

		$options = $this->createMock( \WP_Rocket\CDN\RocketCDN\CDNOptionsManager::class );
		$options->expects( $this->once() )
			->method( 'disable' );

		$data_manager = new DataManagerSubscriber(
			$api,
			$options
		);

		$data_manager->maybe_disable_cdn();
	}
}
