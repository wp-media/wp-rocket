<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

class Settings {
	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instance of Beacon class.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Used CSS table.
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 * @param Beacon       $beacon Beacon instance.
	 * @param UsedCSS      $used_css Used CSS table.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, UsedCSS $used_css ) {
		$this->options  = $options;
		$this->beacon   = $beacon;
		$this->used_css = $used_css;
	}

	/**
	 * Add the RUCSS options to the WP Rocket options array
	 *
	 * @since 3.9
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ): array {
		$options = (array) $options;

		$options['remove_unused_css']          = 0;
		$options['remove_unused_css_safelist'] = [];

		return $options;
	}

	/**
	 * Determines if Remove Unused CSS option is enabled.
	 *
	 * @since 3.9
	 *
	 * @return boolean
	 */
	public function is_enabled(): bool {

		return (bool) $this->options->get( 'remove_unused_css', 0 );
	}

	/**
	 * Sanitizes RUCSS options values when the settings form is submitted
	 *
	 * @since 3.9
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( array $input, AdminSettings $settings ): array {
		$input['remove_unused_css']          = $settings->sanitize_checkbox( $input, 'remove_unused_css' );
		$input['remove_unused_css_safelist'] = ! empty( $input['remove_unused_css_safelist'] ) ? rocket_sanitize_textarea_field( 'remove_unused_css_safelist', $input['remove_unused_css_safelist'] ) : [];

		return $input;
	}

	/**
	 * Set optimize css delivery value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_value( $field_args ): array {
		if ( 'optimize_css_delivery' !== $field_args['id'] ) {
			return $field_args;
		}

		$async_css_value         = (bool) $this->options->get( 'async_css', 0 );
		$remove_unused_css_value = (bool) $this->options->get( 'remove_unused_css', 0 );
		$field_args['value']     = ( $remove_unused_css_value || $async_css_value );

		return $field_args;
	}

	/**
	 * Set optimize css delivery method value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_method_value( $field_args ): array {
		if ( 'optimize_css_delivery_method' !== $field_args['id'] ) {
			return $field_args;
		}

		$value = '';

		if ( (bool) $this->options->get( 'async_css', 0 ) ) {
			$value = 'async_css';
		}

		if ( (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			$value = 'remove_unused_css';
		}

		$field_args['value'] = $value;

		return $field_args;
	}

	/**
	 * Checks if we can display the RUCSS notices
	 *
	 * @param bool $check_enabled check if RUCSS is enabled.
	 *
	 * @since 3.11
	 *
	 * @return bool
	 */
	private function can_display_notice( $check_enabled = true ): bool {
		$screen = get_current_screen();

		if ( ! rocket_direct_filesystem()->is_writable( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) ) ) {
			return false;
		}

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		if ( $check_enabled && ! $this->is_enabled() ) {
			return false;
		}

		return true;
	}

	/**
	 * Disables combine CSS if RUCSS is enabled when updating to 3.11
	 *
	 * @since 3.11
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.11', '>=' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if ( 'local' === wp_get_environment_type() ) {
			$options['optimize_css_delivery'] = 0;
			$options['remove_unused_css']     = 0;
			$options['async_css']             = 0;
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Updates safelist items for new SaaS compatibility
	 *
	 * @since 3.11.0.2
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function update_safelist_items( $old_version ) {
		if ( version_compare( $old_version, '3.11.0.2', '>=' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if ( empty( $options['remove_unused_css_safelist'] ) ) {
			return;
		}

		foreach ( $options['remove_unused_css_safelist'] as $key => $value ) {
			if ( str_contains( $value, '.css' ) ) {
				continue;
			}

			if ( str_starts_with( $value, '(' ) ) {
				continue;
			}

			$options['remove_unused_css_safelist'][ $key ] = '(.*)' . $value;
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Display a notice on table missing.
	 *
	 * @return void
	 */
	public function display_no_table_notice() {

		if ( ! $this->can_display_notice() ) {
			return;
		}
		if ( $this->used_css->exists() ) {
			return;
		}

		// translators: %1$s = plugin name, %2$s = table name, %3$s = <a> open tag, %4$s = </a> closing tag.
		$main_message   = esc_html__( '%1$s: Could not create the %2$s table in the database which is necessary for the Remove Unused CSS feature to work. Please check our %3$sdocumentation%4$s.', 'rocket' );
		$rucss_database = $this->beacon->get_suggest( 'rucss_database' );

		$message = sprintf(
		// translators: %1$s = plugin name, %2$s = table name, %3$s = <a> open tag, %4$s = </a> closing tag.
			$main_message,
			'<strong>WP Rocket</strong>',
			$this->used_css->get_name(),
			'<a href="' . esc_url( $rucss_database['url'] ) . '" data-beacon-article="' . esc_attr( $rucss_database['id'] ) . '" target="_blank" rel="noopener">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
				'id'          => 'rocket-notice-rucss-missing-table',
			]
		);
	}
}
