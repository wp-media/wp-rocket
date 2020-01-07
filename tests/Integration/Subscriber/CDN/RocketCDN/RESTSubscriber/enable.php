<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\RESTfulTestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::enable
 * @group  RocketCDN
 */
class Test_Enable extends RESTfulTestCase {

	/**
	 * Test should update the option settings when the "enable" endpoint is requested.
	 */
	public function testShouldUpdateRocketSettingsWhenEndpointRequest() {
		$this->requestEnableEndpoint();

		$this->assertSame(
			[
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://rocketcdn.me' ],
				'cdn_zone'   => [ 'all' ],
			],
			get_option( 'wp_rocket_settings' )
		);
		$this->assertSame( 1, get_option( 'wp_rocket_rocketcdn_active' ) );
	}


	/**
	 * Test should delete the transient when the "enable" endpoint is requested.
	 */
	public function testShouldDeleteTransientWhenEndpointRequestt() {
		// Set up the transient.
		set_transient( 'rocketcdn_status', 'some value', WEEK_IN_SECONDS );

		// Request the "disable" endpoint.
		$this->requestEnableEndpoint();

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Test should return success packet when the "enable" endpoint is requested.
	 */
	public function testShouldReturnSuccessWhenEndpointRequest() {
		$expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN enabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertSame( $expected, $this->requestEnableEndpoint() );
	}
}
