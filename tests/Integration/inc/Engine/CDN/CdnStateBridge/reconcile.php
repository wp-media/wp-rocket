<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::reconcile
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_Reconcile extends AdminTestCase {

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

		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', 'reconcile', 5 );

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
	}

	/**
	 * Restore option/transient state so this test can't leak into others.
	 *
	 * @return void
	 */
	public function tear_down() {
		update_option( 'wp_rocket_settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );

		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReconcileAsExpected( array $config, array $expected ) {
		set_transient( 'rocketcdn_status', $config['subscription'] ?? [ 'subscription_status' => 'none' ], MINUTE_IN_SECONDS );

		// Seed a self-consistent baseline. Whichever side the bridge reconciles against here,
		// the two sides already agree, so this write settles without correcting anything.
		$settings = array_merge( get_option( 'wp_rocket_settings', [] ), $config['initial'] );
		update_option( 'wp_rocket_settings', $settings );

		// The write under test: only one side of the compatibility pair changes.
		$settings = array_merge( get_option( 'wp_rocket_settings' ), $config['write'] );
		update_option( 'wp_rocket_settings', $settings );

		$result = get_option( 'wp_rocket_settings' );

		foreach ( $expected as $key => $value ) {
			$this->assertSame( $value, $result[ $key ], "Mismatch for '$key'" );
		}
	}
}
