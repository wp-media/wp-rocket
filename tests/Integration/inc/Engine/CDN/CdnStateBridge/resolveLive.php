<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_ResolveLive extends AdminTestCase {
	/**
	 * Settings present before this test, restored in tear_down.
	 *
	 * @var array
	 */
	private $original_settings;

	public function set_up() {
		parent::set_up();

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public function tear_down() {
		update_option( 'wp_rocket_settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldResolveCdnStateLive( array $config, string $expected ) {
		set_transient( 'rocketcdn_status', $config['subscription'] ?? [ 'subscription_status' => 'none' ], MINUTE_IN_SECONDS );

		if ( ! empty( $config['token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['token'] );
		}

		$settings = array_merge( get_option( 'wp_rocket_settings', [] ), $config['stored'] );
		update_option( 'wp_rocket_settings', $settings );

		if ( isset( $config['force'] ) ) {
			add_filter(
				'pre_get_rocket_option_' . $config['force']['option'],
				function () use ( $config ) {
					return $config['force']['value'];
				}
			);
		}

		$this->assertSame( $expected, get_rocket_option( 'cdn_state' ) );

		$stored = get_option( 'wp_rocket_settings' );

		$this->assertSame( $config['stored']['cdn'], $stored['cdn'], 'The stored option itself must be untouched - resolution happens on read, not by writing.' );
		$this->assertSame( $config['stored']['cdn_type'], $stored['cdn_type'] );
		$this->assertSame( $config['stored']['cdn_state'], $stored['cdn_state'] );
	}
}
