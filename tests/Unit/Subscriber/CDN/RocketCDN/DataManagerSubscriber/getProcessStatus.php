<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::get_process_status
 * @group  RocketCDN
 */
class Test_GetProcessStatus extends TestCase {
	public function testShouldSendSuccessWhenOptionExists() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( true );

		Functions\expect( 'wp_send_json_success' )
			->once();

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->get_process_status();
	}

	public function testShouldSendErrorWhenOptionNotExists() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( false );

		Functions\expect( 'wp_send_json_error' )
			->once();

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->get_process_status();
	}
}
