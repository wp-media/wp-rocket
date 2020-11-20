<?php

namespace WP_Rocket\Engine\Media\ImagesDimensions;

use WP_Rocket\Engine\Admin\Settings\Settings;

class ImagesDimensions {
	/**
	 * Adds the images dimensions option to WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['images_dimensions'] = 0;

		return $options;
	}

	/**
	 * Sanitizes the option value when saving from the settings page
	 *
	 * @since 3.8
	 *
	 * @param array    $input    Array of sanitized values after being submitted by the form.
	 * @param Settings $settings Settings class instance.
	 * @return array
	 */
	public function sanitize_option_value( array $input, Settings $settings ) : array {
		$input['images_dimensions'] = $settings->sanitize_checkbox( $input, 'images_dimensions' );

		return $input;
	}
}
