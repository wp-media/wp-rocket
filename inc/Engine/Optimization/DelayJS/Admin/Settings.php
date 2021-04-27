<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;

class Settings {
	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @since 3.9 Removed delay_js_scripts key, added delay_js_exclusions.
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) : array {
		$options = (array) $options;

		$options['delay_js']            = 1;
		$options['delay_js_exclusions'] = [];

		return $options;
	}

	/**
	 * Sets the delay_js_exclusions default value for users with delay JS enabled on upgrade
	 *
	 * @since 3.9 Sets the delay_js_exclusions default value if delay_js is 1
	 * @since 3.7
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.9', '>' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		$options['delay_js_exclusions'] = [];

		if (
			isset( $options['delay_js'] )
			&&
			1 === (int) $options['delay_js']
		) {
			$options['delay_js_exclusions']   = [
				$this->get_excluded_internal_paths(),
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'js-(before|after)',
			];
			$options['minify_concatenate_js'] = 0;
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Sanitizes delay JS options when saving the settings
	 *
	 * @since 3.9
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( $input, $settings ) : array {
		$input['delay_js']            = $settings->sanitize_checkbox( $input, 'delay_js' );
		$input['delay_js_exclusions'] = ! empty( $input['delay_js_exclusions'] ) ? rocket_sanitize_textarea_field( 'delay_js_exclusions', $input['delay_js_exclusions'] ) : [];

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
	 * Gets a regex pattern of excluded paths for wp-content and wp-includes
	 *
	 * @since 3.9
	 *
	 * @return string
	 */
	private function get_excluded_internal_paths() : string {
		$wp_content  = wp_parse_url( content_url(), PHP_URL_PATH );
		$wp_includes = wp_parse_url( includes_url(), PHP_URL_PATH );
		$pattern     = '(?:placeholder)(.*)';
		$paths       = [];

		if (
			! $wp_content
			&&
			! $wp_includes
		) {
			return '';
		}

		if ( $wp_content ) {
			$paths[] = $wp_content;
		}

		if ( $wp_includes ) {
			$paths[] = $wp_includes;
		}

		return str_replace( 'placeholder', implode( '|', $paths ), $pattern );
	}
}
