<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::disable
 * @group  RocketCDN
 */
class Test_Disable extends TestCase {
	public function testShouldSendJSONSuccess() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$time = time();
		
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocketcdn_check_subscription_status_event' )
			->andReturn( $time );
		Functions\expect( 'wp_unschedule_event' )
			->once()
			->with( $time, 'rocketcdn_check_subscription_status_event' );
		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_process' );
		Functions\expect( 'wp_send_json_success' )
			->once()
			->with( 'rocketcdn_disabled' );

		$options = $this->createMock( \WP_Rocket\CDN\RocketCDN\CDNOptionsManager::class );
		$options->expects( $this->once() )
			->method( 'disable' );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$options
		);

		$data_manager->disable();
	}
}
