<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as RocketCDNTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::restore_rocketcdn_free_with_rocket_license_active
 *
 * Symmetric counterpart to disable_rocketcdn_free_with_rocket_license_expired() - proves
 * the licence-expiry force-off documented as "non-destructive" in this PR's design
 * actually gets undone on renewal, instead of leaving cdn_state permanently 'nothing'.
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_RestoreRocketcdnFreeWithRocketLicenseActive extends RocketCDNTestCase {
	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	/**
	 * Restore state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION );

		parent::tear_down();
	}

	/**
	 * The full expire -> renew cycle: disable_rocketcdn_free_with_rocket_license_expired()
	 * sets the tracking flag and forces cdn_state to 'nothing'; on renewal, this method
	 * must see that flag, clear it, and restore cdn_state back to 'rocketcdn_free'.
	 *
	 * @return void
	 */
	public function testShouldRestorePriorStateWhenForcedOffByExpiry() {
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = 'rocketcdn_free';
		update_option( 'wp_rocket_settings', $settings );

		$this->subscriber->disable_rocketcdn_free_with_rocket_license_expired();

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'nothing', $settings['cdn_state'], 'Sanity check on the forced-off state.' );

		$this->subscriber->restore_rocketcdn_free_with_rocket_license_active();

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'rocketcdn_free', $settings['cdn_state'] );
		$this->assertFalse( (bool) get_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION ) );
	}

	/**
	 * Without the tracking flag (licence never actually forced anything off - e.g. a
	 * user who simply never activated RocketCDN Free), this must be a no-op: nothing
	 * to restore, and it must never fabricate an activation the user never had.
	 *
	 * @return void
	 */
	public function testShouldNotRestoreWithoutTrackingFlag() {
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = 'nothing';
		update_option( 'wp_rocket_settings', $settings );

		delete_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION );

		$this->subscriber->restore_rocketcdn_free_with_rocket_license_active();

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'nothing', $settings['cdn_state'] );
	}

	/**
	 * If the user deliberately switched to a different driver (or explicitly re-enabled
	 * something else) while the licence was expired, that choice must survive renewal -
	 * restoring must not clobber a state that is no longer 'nothing'.
	 *
	 * @return void
	 */
	public function testShouldNotRestoreWhenUserChangedStateWhileExpired() {
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = 'rocketcdn_free';
		update_option( 'wp_rocket_settings', $settings );

		$this->subscriber->disable_rocketcdn_free_with_rocket_license_expired();

		// User manually switches to BYOCDN while the licence is still expired.
		$settings              = get_option( 'wp_rocket_settings' );
		$settings['cdn_state'] = 'byocdn';
		update_option( 'wp_rocket_settings', $settings );

		$this->subscriber->restore_rocketcdn_free_with_rocket_license_active();

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'byocdn', $settings['cdn_state'] );
		$this->assertFalse( (bool) get_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION ), 'Tracking flag must still be cleared even though the state itself was left alone.' );
	}
}
