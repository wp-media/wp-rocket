<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::set_cdn_zone
 *
 * @group RocketCDN
 */
class Test_SetCdnZone extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'pre_get_rocket_option_cdn_zone', 'set_cdn_zone', 9 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'pre_get_rocket_option_cdn_zone' );

		delete_transient( 'rocketcdn_status' );
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedZone( $config, $expected ) {
		// Mock HTTP to prevent remote calls when transient is missing.
		add_filter( 'pre_http_request', function () {
			return [
				'response' => [ 'code' => 200 ],
				'body'     => '{}',
			];
		} );

		if ( ! empty( $config['cdn_type'] ) ) {
			add_filter( 'pre_get_rocket_option_cdn_type', function () use ( $config ) {
				return $config['cdn_type'];
			} );
		}

		if ( ! empty( $config['subscription_data'] ) ) {
			set_transient( 'rocketcdn_status', $config['subscription_data'], MINUTE_IN_SECONDS );
		}

		$result = apply_filters( 'pre_get_rocket_option_cdn_zone', null, [] );

		$this->assertSame( $expected['cdn_zone'], $result );
	}
}
