<?php
namespace WP_Rocket\ThirdParty\Themes;

class Themify extends ThirdpartyTheme {

	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'themify';

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_current_theme() ) {
			return [];
		}

		return [
			'init'              => 'disabling_concat_on_rucss',
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ) => 'disabling_concat_on_rucss',
			'themify_save_data' => 'disable_concat_on_saving_data',
		];
	}

	/**
	 * Disable concat on saving theme options.
	 *
	 * @param array $value theme options.
	 * @return mixed
	 */
	public function disable_concat_on_saving_data( $value ) {
		if ( ! apply_filters( 'rocket_disable_rucss_setting', false ) ) {
			return $value;
		}
		$value['setting-dev-mode-concate'] = false;
		return $value;
	}

	/**
	 * Disable concat on RUCSS enabled.
	 *
	 * @return void
	 */
	public function disabling_concat_on_rucss() {
		$data = themify_get_data();

		if ( ! apply_filters( 'rocket_disable_rucss_setting', false ) ) {
			return;
		}

		if ( ! rocket_has_constant( 'THEMIFY_DEV' ) ) {
			define( 'THEMIFY_DEV', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		if ( key_exists( 'setting-dev-mode-concate', $data ) && ! $data['setting-dev-mode-concate'] ) {
			return;
		}

		$data['setting-dev-mode-concate'] = false;

		themify_set_data( $data );
	}
}
