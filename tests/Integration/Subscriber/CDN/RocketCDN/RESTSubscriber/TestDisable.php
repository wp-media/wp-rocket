<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rest_Request;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::disable
 * @group  RocketCDN
 */
class TestDisable extends TestCase {

	/**
	 * Test should update the option settings when the "disable" endpoint is requested.
	 */
	public function testShouldUpdateRocketSettingsWhenDisableRequest() {
		// Set up the settings.
		update_option(
			'wp_rocket_settings',
			[
				'cdn'        => 1,
				'cdn_cnames' => [ 'example1.com', 'example2.com' ],
				'cdn_zone'   => [ 'all' ],
			]
		);
		update_option( 'wp_rocket_rocketcdn_active', 1 );

		// Request the "disable" endpoint.
		$this->requestDisableEndpoint();

		$this->assertSame(
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
			get_option( 'wp_rocket_settings' )
		);
		$this->assertSame( 0, get_option( 'wp_rocket_rocketcdn_active' ) );
	}

	/**
	 * Test should delete the transient when the "disable" endpoint is requested.
	 */
	public function testShouldDeleteTransientWhenDisableRequest() {
		// Set up the transient.
		set_transient( 'rocketcdn_status', 'some value', WEEK_IN_SECONDS );

		// Request the "disable" endpoint.
		$this->requestDisableEndpoint();

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Runs the RESTful endpoint which invokes WordPress to run in an integrated fashion. Callback will be fired.
	 */
	protected function requestDisableEndpoint() {
		$request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'email' => '',
				'key'   => '',
			]
		);

		return rest_do_request( $request );
	}
}
