<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::set_cdn_cnames
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_SetCdnCnames extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'pre_get_rocket_option_cdn_cnames', 'set_cdn_cnames', 9 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'pre_get_rocket_option_cdn_cnames' );

		delete_transient( 'rocketcdn_status' );
		remove_all_filters( 'pre_http_request' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedCnames( $config, $expected ) {
		// Mock HTTP to prevent remote calls when transient is missing.
		add_filter( 'pre_http_request', function () {
			return [
				'response' => [ 'code' => 200 ],
				'body'     => '{}',
			];
		} );

		if ( ! empty( $config['subscription_data'] ) ) {
			set_transient( 'rocketcdn_status', $config['subscription_data'], MINUTE_IN_SECONDS );
		}

		$result = apply_filters( 'pre_get_rocket_option_cdn_cnames', null, [] );

		$this->assertSame( $expected['cdn_cnames'], $result );
	}
}
