<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Integration test covering the full hook chain for the cdn_state migration.
 *
 * Fires the real `wp_rocket_upgrade` hook with real CdnStateBridge + real Options so that
 * the ordering dependency between on_update_add_cdn_type_option (prio 10) and
 * on_update_add_cdn_state_option (prio 11) — and reconcile()'s guard — are exercised
 * end-to-end rather than in mocked isolation.
 *
 * @covers \WP_Rocket\Engine\CDN\Subscriber::on_update_add_cdn_state_option
 * @covers \WP_Rocket\Engine\CDN\Subscriber::on_update_add_cdn_type_option
 *
 * @group  CDN
 * @group  AdminOnly
 */
class Test_OnUpdateAddCdnStateOption extends AdminTestCase {

	/**
	 * Settings present before this test, restored in tear_down.
	 *
	 * @var array
	 */
	private $original_settings;

	/**
	 * @var Options
	 */
	private $options;

	public function set_up() {
		parent::set_up();

		$this->options          = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options->get( 'settings', [] );
	}

	public function tear_down() {
		// Safety net: if the test failed before restoreWpHook ran, put callbacks back now.
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		remove_filter( 'pre_get_rocket_option_cdn', '__return_true', PHP_INT_MAX );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'getCdnCnames' ], PHP_INT_MAX );
		delete_transient( 'rocketcdn_status' );

		// Write original settings back without triggering reconcile cascade.
		$this->unregisterAllCallbacks( 'update_option_wp_rocket_settings' );
		$this->options->set( 'settings', $this->original_settings );
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	public function getCdnCnames(): array {
		return [ 'https://cdn.example.org/' ];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldMigrateAsExpected( array $config, array $expected ) {
		set_transient( 'rocketcdn_status', $config['subscription'], MINUTE_IN_SECONDS );

		// Control Options_Data in-memory reads used by on_update_add_cdn_type_option.
		if ( ! empty( $config['cdn_enabled'] ) ) {
			add_filter( 'pre_get_rocket_option_cdn', '__return_true', PHP_INT_MAX );
		}
		if ( ! empty( $config['cdn_cnames'] ) ) {
			add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'getCdnCnames' ], PHP_INT_MAX );
		}

		// Write the initial DB state without triggering update_option_wp_rocket_settings
		// callbacks, so reconcile() doesn't pre-seed cdn_state before the migration runs.
		$this->unregisterAllCallbacks( 'update_option_wp_rocket_settings' );
		$this->options->set( 'settings', array_merge( $this->original_settings, $config['initial_options'] ) );
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		// Count every update_option_wp_rocket_settings call triggered by wp_rocket_upgrade.
		$write_count = 0;
		$counter     = static function () use ( &$write_count ) {
			$write_count++;
		};
		add_action( 'update_option_wp_rocket_settings', $counter, PHP_INT_MAX, 0 );

		do_action( 'wp_rocket_upgrade', '3.23.4', $config['old_version'] );

		remove_action( 'update_option_wp_rocket_settings', $counter, PHP_INT_MAX );

		$final = $this->options->get( 'settings', [] );

		$this->assertSame(
			$expected['cdn_state'],
			$final['cdn_state'] ?? null,
			'cdn_state mismatch after migration'
		);
		$this->assertSame(
			$expected['write_count'],
			$write_count,
			'DB write count mismatch — triple-write regression guard'
		);
	}
}
