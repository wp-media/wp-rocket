<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::maybe_update_api_status
 * @group  RocketCDN
 */
class Test_MaybeUpdateAPIStatus extends TestCase {

	public function testShouldReturNullWhenMissingIndex() {
		$old_value = [];
		$value     = [];

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$this->assertNull( $data_manager->maybe_update_api_status( $old_value, $value ) );
	}

	public function testShouldReturNullWhenCDNIsSameValue() {
		$old_value = [
			'cdn' => 1,
		];
		$value     = [
			'cdn' => 1,
		];

		$data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$this->assertNull( $data_manager->maybe_update_api_status( $old_value, $value ) );
	}

	public function testShouldSendUpdateRequestWhenCDNIsNotSameValue() {
		$old_value = [
			'cdn' => 0,
		];
		$value     = [
			'cdn' => 1,
		];

		$api = $this->createMock( \WP_Rocket\CDN\RocketCDN\APIClient::class );
		$api->expects( $this->once() )
			->method( 'update_website_status' )
			->with( true );

		$data_manager = new DataManagerSubscriber(
			$api,
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
		);

		$data_manager->maybe_update_api_status( $old_value, $value );
	}
}
