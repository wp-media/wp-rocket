<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::enable
 * @group  RocketCDN
 */
class Test_Enable extends TestCase {
	public function testShouldSendJSONErrorWhenCDNUrlEmpty() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'cdn_url_empty',
			] );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->enable();
	}

	public function testShouldSendJSONErrorWhenCDNUrlInvalid() {
		$_POST['cdn_url'] = 'invalid';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'esc_url_raw' )->justReturn( '' );

		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'cdn_url_invalid_format',
			] );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->enable();
	}

	public function testShouldSendJSONSuccessWhenCDNUrlValid() {
		$_POST['cdn_url'] = 'https://rocketcdn.me';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'esc_url_raw' )->returnArg();

		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocketcdn_check_subscription_status_event' )
			->andReturn( false );
		Functions\expect( 'wp_schedule_single_event' )
			->once();
		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_process' );
		Functions\expect( 'wp_send_json_success' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'rocketcdn_enabled',
			] );

		$api = $this->createMock( \WP_Rocket\CDN\RocketCDN\APIClient::class );
		$api->expects( $this->once() )
			->method( 'get_subscription_data' )
			->willReturn( [
				'subscription_next_date_update' => time(),
			] );

		$options = $this->createMock( \WP_Rocket\CDN\RocketCDN\CDNOptionsManager::class );
		$options->expects( $this->once() )
			->method( 'enable' )
			->with( 'https://rocketcdn.me' );

		$data_manager = new DataManagerSubscriber(
			$api,
			$options
		);

		$data_manager->enable();
	}
}
