<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\CDN\Context;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_meta_boxes_fields'      => [ 'add_meta_box', 9 ],
			'rocket_hidden_settings_fields' => [ 'add_cdn_type', 10 ],
			'rocket_input_sanitize'         => 'sanitize_cdn_type_option',
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
	 * Add CDN to the list of hidden settings fields.
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
}
