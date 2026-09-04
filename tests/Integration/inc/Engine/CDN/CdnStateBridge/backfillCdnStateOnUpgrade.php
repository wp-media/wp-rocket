<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::backfill_cdn_state_on_upgrade
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_BackfillCdnStateOnUpgrade extends AdminTestCase {

	/**
	 * CdnStateBridge instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\CdnStateBridge
	 */
	private $bridge;

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

		// The method writes through options_api->set(), which re-fires
		// update_option_wp_rocket_settings - keep only reconcile() on it, same as its own
		// test does, since we call the method directly rather than firing wp_rocket_upgrade
		// (avoiding every other unrelated upgrade subscriber's side effects entirely).
		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', 'reconcile', 5 );

		$container    = apply_filters( 'rocket_container', null );
		$this->bridge = $container->get( 'cdn_state_bridge' );

		$this->original_settings = get_option( 'wp_rocket_settings', [] );

		// If a subscription-creation test left this set (e.g. it's meant to persist
		// across the request, only cleared on the next check), it makes
		// is_subscription_creation_loading() short-circuit get_subscription_data() to
		// [] here, silently breaking is_paid()/is_free() regardless of the transient
		// this test sets below. Guard against it rather than trusting other tests'
		// cleanup.
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
	}

	/**
	 * Restore option/transient state so this test can't leak into others.
	 *
	 * @return void
	 */
	public function tear_down() {
		update_option( 'wp_rocket_settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );

		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBackfillAsExpected( array $config, array $expected ) {
		set_transient( 'rocketcdn_status', $config['subscription'] ?? [ 'subscription_status' => 'none' ], MINUTE_IN_SECONDS );

		if ( ! empty( $config['token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['token'] );
		}

		$settings = array_merge( get_option( 'wp_rocket_settings', [] ), $config['initial'] );
		update_option( 'wp_rocket_settings', $settings );

		$this->bridge->backfill_cdn_state_on_upgrade();

		$result = get_option( 'wp_rocket_settings' );

		foreach ( $expected as $key => $value ) {
			$this->assertSame( $value, $result[ $key ], "Mismatch for '$key'" );
		}
	}
}
