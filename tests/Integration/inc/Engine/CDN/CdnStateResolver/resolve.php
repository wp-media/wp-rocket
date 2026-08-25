<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateResolver;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Proves the scenario CdnStateBridge cannot handle: a filter forcing `cdn`/`cdn_type` at read
 * time, with no corresponding option write, must still be reflected in cdn_state immediately.
 *
 * `Render\Controller::maybe_pause_cdn_for_inactive_subscription()` is exactly this - it forces
 * `cdn` to false via `pre_get_rocket_option_cdn` without ever writing to `wp_rocket_settings`.
 * A third-party plugin using the same extension point behaves identically from cdn_state's
 * point of view. This test uses a bare filter to stand in for either.
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_Resolve extends AdminTestCase {
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
		remove_all_filters( 'pre_get_rocket_option_cdn' );

		parent::tear_down();
	}

	public function testShouldReflectAReadTimeForcedValueWithNoAssociatedWrite() {
		$settings = array_merge(
			get_option( 'wp_rocket_settings', [] ),
			[
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			]
		);
		update_option( 'wp_rocket_settings', $settings );

		$this->assertSame( 'byocdn', get_rocket_option( 'cdn_state' ) );

		// Simulate maybe_pause_cdn_for_inactive_subscription() (or any third party) forcing
		// `cdn` off at read time. No option write happens - this is the whole point.
		add_filter(
			'pre_get_rocket_option_cdn',
			function () {
				return false;
			}
		);

		$this->assertSame(
			'nothing',
			get_rocket_option( 'cdn_state' ),
			'cdn_state must follow a read-time forced value even though nothing was written to the option.'
		);

		$stored = get_option( 'wp_rocket_settings' );

		$this->assertSame( 1, $stored['cdn'], 'The stored option itself must be untouched - resolution happens on read, not by writing.' );
		$this->assertSame( 'byocdn', $stored['cdn_type'] );
		$this->assertSame( 'byocdn', $stored['cdn_state'] );
	}
}
