<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::enable
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
 *
 * @group  RocketCDN
 */
class Test_Enable extends ApiTestCase {

	/**
	 * Test should update the option settings when the "enable" endpoint is requested.
	 */
	public function testShouldUpdateRocketSettingsWhenEndpointRequest() {
		$body_params = [
			'email' => self::getApiCredential( 'ROCKET_EMAIL' ),
			'key'   => self::getApiCredential( 'ROCKET_KEY' ),
			'url'   => 'https://rocketcdn.me',
		];

		$this->requestEnableEndpoint( $body_params );

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
		$body_params = [
			'email' => self::getApiCredential( 'ROCKET_EMAIL' ),
			'key'   => self::getApiCredential( 'ROCKET_KEY' ),
			'url'   => 'https://rocketcdn.me',
		];

		// Set up the transient.
		set_transient( 'rocketcdn_status', 'some value', WEEK_IN_SECONDS );

		// Request the "disable" endpoint.
		$this->requestEnableEndpoint( $body_params );

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}

	/**
	 * Test should return success packet when the "enable" endpoint is requested.
	 */
	public function testShouldReturnSuccessWhenEndpointRequest() {
		$body_params = [
			'email' => self::getApiCredential( 'ROCKET_EMAIL' ),
			'key'   => self::getApiCredential( 'ROCKET_KEY' ),
			'url'   => 'https://rocketcdn.me',
		];

		$expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN enabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertSame( $expected, $this->requestEnableEndpoint( $body_params ) );
	}
}
