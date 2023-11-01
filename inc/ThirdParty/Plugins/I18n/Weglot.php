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
		if ( ! function_exists( 'weglot_get_request_url_service' ) || ! function_exists( 'weglot_get_current_full_url' ) ) {
			return $referer;
		}

		return weglot_get_request_url_service()->url_to_relative( weglot_get_current_full_url() );
	}
}
