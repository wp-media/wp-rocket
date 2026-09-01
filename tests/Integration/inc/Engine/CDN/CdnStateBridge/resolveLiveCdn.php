<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live_cdn
 *
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live_cdn
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_ResolveLiveCdn extends AdminTestCase {
	/**
	 * Settings present before this test, restored in tear_down.
	 *
	 * @var array
	 */
	private $original_settings;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
	}

	/**
	 * Restore state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		update_option( 'wp_rocket_settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );

		parent::tear_down();
	}

	/**
	 * Reproduces the reachable bug scenario the fix addresses: a class holding an
	 * Options_Data instance built *before* CDNOptionsManager::enable()/disable() runs
	 * (e.g. a subscriber already instantiated earlier in the same request, the way
	 * Subscriber.php/CDN.php/Render/Controller.php/RocketCDN/Rest.php/Support/Meta.php
	 * are) must still see the new 'cdn' value afterward - even though its own internal
	 * snapshot was never touched and the 'options' container service is not shared
	 * (see class-options.php - add(), not addShared() - so there is no single object a
	 * write could propagate through).
	 */
	public function testShouldReflectEnableAndDisableToAnIndependentlyBuiltInstance() {
		// Context::is_rocketcdn() (used by Render\Subscriber::maybe_pause_cdn_for_inactive_subscription,
		// also hooked on pre_get_rocket_option_cdn) reads 'cdn_type' off its own Options_Data
		// snapshot, which is subject to the exact same DI-container staleness this test isn't
		// exercising - there's no license/subscription set up here for is_forced_paused() to
		// behave predictably. Force 'cdn_type' live so that code path bails out early via its
		// own !is_rocketcdn() guard, isolating this test to 'cdn' liveness only.
		add_filter(
			'pre_get_rocket_option_cdn_type',
			function () {
				return 'byocdn';
			}
		);

		$settings        = get_option( 'wp_rocket_settings', [] );
		$settings['cdn'] = 0;
		update_option( 'wp_rocket_settings', $settings );

		// Simulates a class already holding its own Options_Data snapshot from before
		// CDNOptionsManager::enable() runs later in the same request.
		$early_reader = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings', [] ) );

		$this->assertSame( 0, $early_reader->get( 'cdn' ), 'Sanity check on the pre-enable stored value.' );

		$options_api = new Options( 'wp_rocket_' );
		$cdn_options = new CDNOptionsManager( $options_api, new Options_Data( $options_api->get( 'settings', [] ) ) );

		$cdn_options->enable( false );

		$this->assertSame( 1, $early_reader->get( 'cdn' ), 'The pre-existing instance must resolve the new value live, not its own frozen snapshot.' );

		$cdn_options->disable();

		$this->assertSame( 0, $early_reader->get( 'cdn' ), 'Same for disable().' );
	}
}
