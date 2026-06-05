<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Admin;

use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_meta_boxes_fields'      => [ 'add_meta_box', 9 ],
			'rocket_hidden_settings_fields' => [
				[ 'add_cdn_type' ],
				[ 'add_cdn_drivers_options_hidden_fields' ],
			],
			'rocket_input_sanitize'         => [
				[ 'sanitize_cdn_type_option', 10, 2 ],
				[ 'sanitize_cdn_driver_options', 10, 2 ],
			],
		];
	}

	/**
	 * Add the field to the WP Rocket metabox on the post edit page.
	 *
	 * @param string[] $fields Metaboxes fields.
	 *
	 * @return string[]
	 */
	public function add_meta_box( array $fields ) {
		/*
		 * Hiding the CDN option in the metabox for now.
		 * We will revisit this when handling CDN status for different pages/posts.
		 *
		 * $fields['cdn'] = __( 'CDN', 'rocket' );
		 */

		return $fields;
	}

	/**
	 * Add CDN type to the list of hidden settings fields.
	 *
	 * @param string[] $fields Hidden settings fields.
	 *
	 * @return string[]
	 */
	public function add_cdn_type( array $fields ) {
		$fields[] = 'cdn_type';

		return $fields;
	}

	/**
	 * Sanitize the CDN type option.
	 *
	 * @param array $input Input array.
	 *
	 * @return array
	 */
	public function sanitize_cdn_type_option( array $input ) {
		// Set default value if empty.
		if ( empty( $input['cdn_type'] ) ) {
			$input['cdn_type'] = Context::ROCKETCDN_TYPE;
		}

		$allowed_drivers = [ Context::ROCKETCDN_TYPE, Context::BYOCDN_TYPE ];

		// Validate that the value is one of the allowed drivers.
		if ( ! in_array( $input['cdn_type'], $allowed_drivers, true ) ) {
			$input['cdn_type'] = Context::ROCKETCDN_TYPE;
		}

		// Sanitize the value.
		$input['cdn_type'] = sanitize_text_field( $input['cdn_type'] );

		return $input;
	}

	/**
	 * Add byocdn to the list of hidden settings fields.
	 *
	 * @param string[] $fields Hidden settings fields.
	 *
	 * @return string[]
	 */
	public function add_cdn_drivers_options_hidden_fields( array $fields ) {
		$fields[] = Context::BYOCDN_TYPE;
		$fields[] = Context::ROCKETCDN_TYPE;

		return $fields;
	}

	/**
	 * Sanitize the CDN driver options.
	 *
	 * @param array         $input Input array.
	 * @param AdminSettings $settings Admin settings instance.
	 *
	 * @return array
	 */
	public function sanitize_cdn_driver_options( array $input, AdminSettings $settings ) {
		// Sanitize the value.
		$input[ Context::BYOCDN_TYPE ]    = $settings->sanitize_checkbox( $input, Context::BYOCDN_TYPE );
		$input[ Context::ROCKETCDN_TYPE ] = $settings->sanitize_checkbox( $input, Context::ROCKETCDN_TYPE );

		return $input;
	}
}
