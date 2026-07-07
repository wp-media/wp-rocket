<?php

namespace WP_Rocket\Tests;

trait SettingsTrait {
	public static $before_settings = [];

	public $settings     = [];
	public $old_settings = [];

	public static function getOriginalSettings() {
		self::$before_settings = (array) get_option( 'wp_rocket_settings', [] );
	}

	public static function resetOriginalSettings() {
		self::updateSettingsWithoutSaveSideEffects( self::$before_settings );
	}

	public function setUpSettings() {
		$this->old_settings = array_key_exists( 'settings', $this->config )
			? array_merge( self::$before_settings, $this->config['settings'] )
			: self::$before_settings;
		self::updateSettingsWithoutSaveSideEffects( $this->old_settings );
	}

	public function tearDownSettings() {
		delete_option( 'wp_rocket_settings' );
	}

	public function mergeExistingSettingsAndUpdate( array $settings ) {
		self::updateSettingsWithoutSaveSideEffects( array_merge( $this->old_settings, $settings ) );
	}

	/**
	 * Writes wp_rocket_settings for test fixture purposes only, without triggering
	 * rocket_pre_main_option()'s value mutation (forced cache_ssl, minify key regeneration,
	 * environment-based overrides, DB-optimize cron clearing) or rocket_after_save_options()'s
	 * cache purge and file regeneration. This trait seeds/restores option state for tests; it
	 * isn't simulating a real wp-admin settings save, so it shouldn't pick up either side effect
	 * just because inc/admin/options.php is no longer gated behind is_admin() (see inc/main.php).
	 *
	 * @param array $settings Settings array to persist.
	 * @return void
	 */
	private static function updateSettingsWithoutSaveSideEffects( array $settings ) {
		remove_filter( 'pre_update_option_wp_rocket_settings', 'rocket_pre_main_option', 10 );
		remove_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options', 10 );

		update_option( 'wp_rocket_settings', $settings );

		add_filter( 'pre_update_option_wp_rocket_settings', 'rocket_pre_main_option', 10, 2 );
		add_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options', 10, 2 );
	}
}
