<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;

class Settings {

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	protected $options_api;

	/**
	 * Constructor.
	 *
	 * @param Options $options_api Options instance.
	 */
	public function __construct( Options $options_api ) {
		$this->options_api = $options_api;
	}

	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @since 3.18 Added delay_js_execution_safe_mode.
	 * @since 3.9 Removed delay_js_scripts key, added delay_js_exclusions.
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ): array {
		$options = (array) $options;

		$options['delay_js']                     = 0;
		$options['delay_js_exclusions']          = [];
		$options['delay_js_execution_safe_mode'] = 0;

		return $options;
	}

	/**
	 * Sets the delay_js_exclusions default value for users with delay JS enabled on upgrade
	 *
	 * @since 3.18 Keep the custom delay js exclusions if there were before.
	 * @since 3.9 Sets the delay_js_exclusions default value if delay_js is 1
	 * @since 3.7
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.9', '>=' ) ) {
			return;
		}

		$options = $this->options_api->get( 'settings', [] );

		$options['delay_js_exclusions'] = [];

		if (
			isset( $options['delay_js'] )
			&&
			1 === (int) $options['delay_js']
		) {
			$options['minify_concatenate_js'] = 0;
		}

		$this->options_api->set( 'settings', $options );
	}

	/**
	 * Sanitizes delay JS options when saving the settings
	 *
	 * @since 3.9
	 * @since 3.18 Deletes safe mode exclusions from `delay_js_exclusions`.
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( $input, $settings ): array {
		$input['delay_js']                     = $settings->sanitize_checkbox( $input, 'delay_js' );
		$input['delay_js_execution_safe_mode'] = $settings->sanitize_checkbox( $input, 'delay_js_execution_safe_mode' );
		$input['delay_js_exclusions']          =
			! empty( $input['delay_js_exclusions'] )
				?
				rocket_sanitize_textarea_field( 'delay_js_exclusions', $input['delay_js_exclusions'] )
				:
				[];

		if ( ! empty( $input['delay_js_execution_safe_mode'] ) ) {
			$default_exclusions           = self::get_safe_mode_exclusions();
			$input['delay_js_exclusions'] = array_diff( $input['delay_js_exclusions'], $default_exclusions );
		}

		return $input;
	}

	/**
	 * Disable combine JS option when delay JS is enabled
	 *
	 * @since 3.9
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 */
	public function maybe_disable_combine_js( $value, $old_value ): array {
		if ( ! isset( $value['delay_js'], $value['minify_concatenate_js'] ) ) {
			return $value;
		}

		if (
			0 === $value['minify_concatenate_js']
			||
			0 === $value['delay_js']
		) {
			return $value;
		}

		if (
			isset( $old_value['delay_js'], $old_value['minify_concatenate_js'] )
			&&
			$value['delay_js'] === $old_value['delay_js']
			&&
			1 === $value['delay_js']
			&&
			0 === $old_value['minify_concatenate_js']
		) {
			return $value;
		}

		$value['minify_concatenate_js'] = 0;

		return $value;
	}


	/**
	 * Get the list of default exclusions for the safe mode.
	 *
	 * This method returns an array of regular expressions that match JavaScript files
	 * and patterns which should be excluded from the delay JavaScript execution feature
	 * when the safe mode is enabled.
	 *
	 * @since 3.18
	 *
	 * @return array An array of regular expressions for safe mode exclusions.
	 */
	public static function get_safe_mode_exclusions(): array {
		return [
			'\/jquery(-migrate)?-?([0-9.]+)?(.min|.slim|.slim.min)?.js(\?(.*))?( |\'|"|>)',
			'js-(before|after)',
			'(?:/wp-content/|/wp-includes/)(.*)',
		];
	}

	/**
	 * Get default exclusion list.
	 *
	 * @since 3.9.1
	 *
	 * @return string[]
	 */
	public static function get_delay_js_default_exclusions(): array {

		$exclusions = self::get_safe_mode_exclusions();

		$wp_content  = wp_parse_url( content_url( '/' ), PHP_URL_PATH );
		$wp_includes = wp_parse_url( includes_url( '/' ), PHP_URL_PATH );
		$pattern     = '(?:placeholder)(.*)';
		$paths       = [];

		if ( ! $wp_content && ! $wp_includes ) {
			return $exclusions;
		}

		if ( $wp_content ) {
			$paths[] = $wp_content;
		}

		if ( $wp_includes ) {
			$paths[] = $wp_includes;
		}

		$exclusions[] = str_replace( 'placeholder', implode( '|', $paths ), $pattern );

		return $exclusions;
	}

	/**
	 * Check if current exclusion list has the default list.
	 *
	 * @since 3.9.1
	 *
	 * @return bool
	 */
	public static function exclusion_list_has_default(): bool {
		$current_list = get_rocket_option( 'delay_js_exclusions', [] );
		if ( empty( $current_list ) ) {
			return false;
		}

		$default_list = self::get_delay_js_default_exclusions();
		if ( count( $current_list ) < count( $default_list ) ) {
			return false;
		}

		$current_list = array_flip( $current_list );

		foreach ( $default_list as $item ) {
			if ( ! isset( $current_list[ $item ] ) ) {
				return false;
			}
		}
		return true;
	}
}
