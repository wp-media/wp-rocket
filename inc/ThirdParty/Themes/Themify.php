<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Themify implements Subscriber_Interface {
	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

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
		return [
			'after_switch_theme' => 'disabling_concat_on_theme',
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ) => [ 'disabling_concat_on_rucss', 10, 2 ],
			'themify_save_data'  => 'disable_concat_on_saving_data',
			'themify_dev_mode'   => 'maybe_enable_dev_mode',
		];
	}

	/**
	 * Change the value on change theme.
	 *
	 * @return void
	 */
	public function disabling_concat_on_theme() {
		// @phpstan-ignore-next-line
		$data = themify_get_data();

		$remove_unused_css = $this->options->get( 'remove_unused_css', false );

		if ( ! $remove_unused_css ) {
			$data = $this->maybe_disable( $data );
		}

		if ( $remove_unused_css ) {
			$data = $this->maybe_enable( $data );
		}

		// @phpstan-ignore-next-line
		themify_set_data( $data );
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

		return $this->maybe_enable( $value );
	}

	/**
	 * Disable concat on RUCSS enabled.
	 *
	 * @param array $old Old configurations.
	 * @param array $new New configurations.
	 *
	 * @return void
	 */
	public function disabling_concat_on_rucss( $old, $new ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.newFound

		if ( ! key_exists( 'remove_unused_css', $old ) || ! key_exists( 'remove_unused_css', $new ) || $old['remove_unused_css'] === $new['remove_unused_css'] ) {
			return;
		}

		// @phpstan-ignore-next-line
		$data = themify_get_data();

		if ( ! $new['remove_unused_css'] ) {
			$data = $this->maybe_disable( $data );
		}

		if ( $new['remove_unused_css'] ) {
			$data = $this->maybe_enable( $data );
		}

		// @phpstan-ignore-next-line
		themify_set_data( $data );
	}

	/**
	 * Maybe disable concate CSS.
	 *
	 * @param array $data Themify data.
	 *
	 * @return array
	 */
	protected function maybe_disable( array $data ): array {
		if ( key_exists( 'setting-dev-mode-concate', $data ) && ! $data['setting-dev-mode-concate'] && key_exists( 'setting-dev-mode', $data ) && ! $data['setting-dev-mode'] ) {
			return $data;
		}

		$data['setting-dev-mode-concate'] = false;
		$data['setting-dev-mode']         = false;

		return $data;
	}

	/**
	 * Maybe enable dev mode and concat.
	 *
	 * @param array $data Themify data.
	 *
	 * @return array
	 */
	protected function maybe_enable( array $data ): array {

		if ( key_exists( 'setting-dev-mode-concate', $data ) && $data['setting-dev-mode-concate'] && key_exists( 'setting-dev-mode', $data ) && $data['setting-dev-mode'] ) {
			return $data;
		}

		$data['setting-dev-mode']         = true;
		$data['setting-dev-mode-concate'] = true;

		return $data;
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
