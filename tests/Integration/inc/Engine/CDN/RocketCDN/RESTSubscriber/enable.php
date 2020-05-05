<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::enable
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
 *
 * @group  RocketCDN
 */
class Test_Enable extends ApiTestCase {

	/**
	 * Test should update the option settings when the "enable" endpoint is requested.
	 */
	public function testShouldUpdateRocketSettingsWhenEndpointRequest() {
		$this->requestEnableEndpoint();

		$expected_subset = [
			'cdn'        => 1,
			'cdn_cnames' => [ 'https://rocketcdn.me' ],
			'cdn_zone'   => [ 'all' ],
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
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
