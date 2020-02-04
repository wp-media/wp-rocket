<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::set_process_status
 * @group  RocketCDN
 */
class Test_SetProcessStatus extends TestCase {
	public function testShouldReturnNullWhenStatusEmpty() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$this->assertNull( $data_manager->set_process_status() );
	}

	public function testShouldDeleteOptionWhenStatusFalse() {
		$_POST['status'] = 'false';

		Functions\when( 'check_ajax_referer' )->justReturn( true );

		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_process' );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->set_process_status();
	}

	public function testShouldUpdateOptionWhenStatusTrue() {
		$_POST['status'] = 'true';

		Functions\when( 'check_ajax_referer' )->justReturn( true );

		Functions\expect( 'update_option' )
			->once()
			->with( 'rocketcdn_process', true );

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->set_process_status();
	}
}
