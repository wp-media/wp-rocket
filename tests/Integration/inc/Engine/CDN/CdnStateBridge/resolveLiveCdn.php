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
	 * WP Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

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

		// Explicitly pin admin context rather than depending on ambient screen state left
		// over from whichever test ran previously - Subscriber::apply_pause_on_rocketcdn_only()
		// (also hooked on this same 'cdn' read path) behaves differently in admin vs. front end.
		set_current_screen( 'settings_page_wprocket' );

		$this->options_api       = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options_api->get( 'settings', [] );
	}

	/**
	 * Restore state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->options_api->set( 'settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		remove_all_filters( 'get_rocket_option_cdn' );
		set_current_screen( 'front' );

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

		$settings        = $this->options_api->get( 'settings', [] );
		$settings['cdn'] = 0;
		$this->options_api->set( 'settings', $settings );

		// Simulates a class already holding its own Options_Data snapshot from before
		// CDNOptionsManager::enable() runs later in the same request.
		$early_reader = new Options_Data( $this->options_api->get( 'settings', [] ) );

		$this->assertSame( 0, $early_reader->get( 'cdn' ), 'Sanity check on the pre-enable stored value.' );

		$cdn_options = new CDNOptionsManager( $this->options_api, new Options_Data( $this->options_api->get( 'settings', [] ) ) );

		$cdn_options->enable( false );

		$this->assertSame( 1, $early_reader->get( 'cdn' ), 'The pre-existing instance must resolve the new value live, not its own frozen snapshot.' );

		$cdn_options->disable();

		$this->assertSame( 0, $early_reader->get( 'cdn' ), 'Same for disable().' );
	}

	/**
	 * Resolve_live_cdn() always returns a non-null value, which short-circuits
	 * Options_Data::get() before it would normally apply the get_rocket_option_cdn
	 * post-filter to whatever it found in its internal array. Registers our own
	 * callback on that exact post-filter and asserts it still runs against the live
	 * value - proves resolve_live_cdn() re-applies it explicitly instead of silently
	 * bypassing it.
	 *
	 * Used to instead assert Subscriber::apply_pause_on_rocketcdn_only() (which was
	 * registered on this same post-filter) forced 'cdn' truthy on the front end for a
	 * BYOCDN driver - that behavior was intentionally removed (issue #8707) since it
	 * masked a BYOCDN user's explicit "off" state, so this test now proves the
	 * post-filter mechanism itself with a filter of its own instead.
	 */
	public function testShouldStillApplyGetRocketOptionCdnPostFilterOnTheFrontEnd() {
		set_current_screen( 'front' );

		$settings        = $this->options_api->get( 'settings', [] );
		$settings['cdn'] = 0;
		$this->options_api->set( 'settings', $settings );

		add_filter(
			'get_rocket_option_cdn',
			function ( $value ) {
				return ! $value;
			}
		);

		$this->assertTrue( get_rocket_option( 'cdn' ) );
	}
}
