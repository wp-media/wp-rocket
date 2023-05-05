<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options_Data;

class Themify extends ThirdpartyTheme {

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'themify';

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

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
			'after_setup_theme'  => 'disabling_concat_on_rucss',
			'after_switch_theme' => 'disabling_concat_on_rucss',
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ) => 'disabling_concat_on_rucss',
			'themify_save_data'  => 'disable_concat_on_saving_data',
			'themify_dev_mode'   => 'maybe_enable_dev_mode',
		];
	}

	/**
	 * Disable concat on saving theme options.
	 *
	 * @param array $value theme options.
	 * @return array
	 */
	public function disable_concat_on_saving_data( $value ) {
		if ( ! $this->options->get( 'remove_unused_css', false ) ) {
			return $value;
		}
		$value['setting-dev-mode']         = true;
		$value['setting-dev-mode-concate'] = true;
		return $value;
	}

	/**
	 * Disable concat on RUCSS enabled.
	 *
	 * @return void
	 */
	public function disabling_concat_on_rucss() {
		$data = themify_get_data();

		if ( ! $this->options->get( 'remove_unused_css', false ) ) {
			$this->maybe_disable( $data );
			return;
		}

		if ( ! rocket_has_constant( 'THEMIFY_DEV' ) ) {
			define( 'THEMIFY_DEV', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		if ( key_exists( 'setting-dev-mode-concate', $data ) && $data['setting-dev-mode-concate'] ) {
			return;
		}

		$data['setting-dev-mode']         = true;
		$data['setting-dev-mode-concate'] = true;

		themify_set_data( $data );
	}

	/**
	 * Maybe disable concate CSS.
	 *
	 * @param array $data themify settings.
	 * @return void
	 */
	protected function maybe_disable( array $data ) {
		if ( ! key_exists( 'setting-dev-mode-concate', $data ) || ! $data['setting-dev-mode-concate'] ) {
			return;
		}

		$data['setting-dev-mode-concate'] = false;
		$data['setting-dev-mode']         = false;

		themify_set_data( $data );
	}

	/**
	 * Enable the dev mode when RUCSS is activated.
	 *
	 * @param bool $is_enabled Is dev mode enabled.
	 * @return bool
	 */
	public function maybe_enable_dev_mode( $is_enabled ) {

		if ( $this->options->get( 'remove_unused_css', false ) ) {
			return true;
		}

		return $is_enabled;
	}
}
