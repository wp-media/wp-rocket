<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::enable
 * @group  RocketCDN
 */
class Test_Enable extends TestCase {
	public function testShouldSendJSONErrorWhenNoCapacity() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'unauthorized_user',
			] );

		$data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);

		$data_manager->enable();
	}

	public function testShouldSendJSONErrorWhenCDNUrlEmpty() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );

		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'cdn_url_empty',
			] );

		$data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);

		$data_manager->enable();
	}

	public function testShouldSendJSONErrorWhenCDNUrlInvalid() {
		$_POST['cdn_url'] = 'invalid';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'esc_url_raw' )->justReturn( '' );

		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( [
				'process' => 'subscribe',
				'message' => 'cdn_url_invalid_format',
			] );

		$data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);

		$data_manager->enable();
	}

	public function testShouldSendJSONSuccessWhenCDNUrlValid() {
		$_POST['cdn_url'] = 'https://rocketcdn.me';

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
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

		$api = Mockery::mock( APIClient::class );
		$api->shouldReceive( 'get_subscription_data' )
			->once()
		    ->andReturn( [
			    'subscription_next_date_update' => time(),
		    ] );

		$options = Mockery::mock( CDNOptionsManager::class );
		$options->shouldReceive( 'enable' )
				->once()
		        ->with( 'https://rocketcdn.me' );

		$data_manager = new DataManagerSubscriber(
			$api,
			$options
		);

		$data_manager->enable();
	}
}
