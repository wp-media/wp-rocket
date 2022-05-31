<?php

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with WPML.
 */
class WPML implements Subscriber_Interface {


	/**
	 * Events for subscriber to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return [];
		}

		$events = [
			'wcml_is_cache_enabled_for_switching_currency' => '__return_true',
			'rocket_rucss_is_home_url'                     => [ 'is_home', 10, 2 ],
		];

		return $events;
	}

	/**
	 * Check if current url is home.
	 *
	 * @param bool   $is_home bool returned.
	 * @param string $url url of current page.
	 * @return boolean
	 */
	public static function is_home( $is_home, $url ) {
		/**
		 * Filters the home url.
		 *
		 * @since 3.11.4
		 *
		 * @param string  $url url of homepage.
		 */
		$home_url = apply_filters( 'wpml_home_url', home_url() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		return untrailingslashit( $url ) === untrailingslashit( $home_url );
	}
}
