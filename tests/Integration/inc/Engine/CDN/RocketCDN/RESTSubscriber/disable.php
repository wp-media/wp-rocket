<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::disable
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 *
 * @group  RocketCDN
 */
class Test_Disable extends ApiTestCase {

	/**
	 * Test should update the option settings when the "disable" endpoint is requested.
	 */
	public function testShouldUpdateRocketSettingsWhenEndpointRequest() {
		// Set up the settings.
		update_option(
			'wp_rocket_settings',
			[
				'cdn'        => 1,
				'cdn_cnames' => [ 'example1.com', 'example2.com' ],
				'cdn_zone'   => [ 'all' ],
			]
		);

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
	}

	/**
	 * Test should delete the transient when the "disable" endpoint is requested.
	 */
	public function testShouldDeleteTransientWhenEndpointRequest() {
		// Set up the transient.
		set_transient( 'rocketcdn_status', 'some value', WEEK_IN_SECONDS );

		// Request the "disable" endpoint.
		$this->requestDisableEndpoint();

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Test should return success packet when the "disable" endpoint is requested.
	 */
	public function testShouldReturnSuccessWhenEndpointRequest() {
		$expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN disabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertSame( $expected, $this->requestDisableEndpoint() );
	}
}
