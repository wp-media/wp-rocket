<?php

namespace WP_Rocket\Engine\Media\Lazyload;

use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_first_install_options' => [ 'add_option', 15 ],
			'rocket_input_sanitize'        => 'sanitize_exclude_lazyload',
		];
	}

	/**
	 * Adds the exclude lazyload option to WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['exclude_lazyload'] = [];

		return $options;
	}

	/**
	 * Sanitizes the exclude lazyload input when saving the option
	 *
	 * @since 3.8
	 *
	 * @param array $input Input array.
	 * @return array
	 */
	public function sanitize_exclude_lazyload( array $input ) : array {
		if ( empty( $input['exclude_lazyload'] ) ) {
			$input['exclude_lazyload'] = [];
		}

		$input['exclude_lazyload'] = rocket_sanitize_textarea_field( 'exclude_lazyload', $input['exclude_lazyload'] );

		return $input;
	}
}
