<?php

namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * Events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options' => 'add_option',
		];
	}

	/**
	 * Adds the option key & value to the WP Rocket options array
	 *
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( $options ) {
		$options = (array) $options;

		$options['preload_links'] = 0;

		return $options;
	}
}
