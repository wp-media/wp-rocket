<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::disable
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 *
 * @group  RocketCDN
 */
class Test_Disable extends ApiTestCase {

	private $original_settings;

	public function set_up() {
		parent::set_up();
		$this->original_settings = get_option( 'wp_rocket_settings' );
	}

	public function tear_down() {
		update_option( 'wp_rocket_settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		parent::tear_down();
	}

	public function testShouldUpdateRocketSettingsWhenEndpointRequest() {
		// Set up the transient.
		set_transient( 'rocketcdn_status', 'some value', WEEK_IN_SECONDS );

		// Set up the settings.
		update_option(
			'wp_rocket_settings',
			[
				'cdn'        => 1,
				'cdn_cnames' => [ 'example1.com', 'example2.com' ],
				'cdn_zone'   => [ 'all' ],
			]
		);

		$expected_response = [
			'code'    => 'success',
			'message' => __( 'RocketCDN disabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$body_params = [
			'email' => self::getApiCredential( 'ROCKET_EMAIL' ),
			'key'   => self::getApiCredential( 'ROCKET_KEY' ),
		];

		// Request the "disable" endpoint.
		$this->assertSame( $expected_response, $this->requestDisableEndpoint( $body_params ) );

		$options = get_option( 'wp_rocket_settings' );

		$this->assertArrayHasKey( 'cdn', $options );
		$this->assertSame( 0, $options['cdn'] );

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}
}
