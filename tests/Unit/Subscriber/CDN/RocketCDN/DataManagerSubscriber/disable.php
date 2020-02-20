<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::disable
 * @group  RocketCDN
 */
class Test_Disable extends TestCase {
	public function testShouldSendJSONErrorWhenNoCapacity() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'unsubscribe',
				'message' => 'unauthorized_user',
			] );
	
			$data_manager = new DataManagerSubscriber(
				$this->createMock( APIClient::class ),
				$this->createMock( CDNOptionsManager::class )
			);
	
			$data_manager->disable();
	}

	public function testShouldSendJSONSuccess() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );

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
			->with( [
				'process' => 'unsubscribe',
				'message' => 'rocketcdn_disabled',
			] );

		$options = $this->createMock( CDNOptionsManager::class );
		$options->expects( $this->once() )
		        ->method( 'disable' );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( APIClient::class ),
			$options
		);

		$data_manager->disable();
	}
}
