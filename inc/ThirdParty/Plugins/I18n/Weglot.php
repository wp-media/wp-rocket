<?php

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Weglot.
 */
class Weglot implements Subscriber_Interface {

	/**
	 *  Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array|string[]
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'Context_Weglot' ) ) {
			return [];
		}

		return [
			'rocket_admin_bar_referer' => 'add_langs_to_referer',
		];
	}

	/**
	 * Modify the referer URL by appending the current language from Weglot as a prefix to the URL path.
	 *
	 * @param string $referer The original referer URL.
	 * @return string The modified referer URL with the language as a prefix.
	 */
	public function add_langs_to_referer( $referer ) {
		if ( ! function_exists( 'weglot_get_current_language' ) ) {
			return $referer;
		}
		$current_language = weglot_get_current_language();

		// Check if the referer URL contains query parameters.
		$parsed_url = wp_parse_url( $referer );
		// Append the language as a prefix to the path.
		$new_path = user_trailingslashit( '/' . $current_language . $parsed_url['path'] );

		if ( isset( $parsed_url['query'] ) ) {
			// If there are query parameters, append them to the modified path.
			return $new_path . '?' . $parsed_url['query'];
		}

		return $new_path;
	}
}
