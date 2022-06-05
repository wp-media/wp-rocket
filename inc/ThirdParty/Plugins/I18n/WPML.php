<?php

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Subscriber for compatibility with WPML.
 */
class WPML implements Subscriber_Interface {
	use ReturnTypesTrait;

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
			'wcml_is_cache_enabled_for_switching_currency' => 'return_true',
			'rocket_rucss_is_home_url'                     => [ 'is_home', 10, 2 ],
		];

		return $events;
	}

	/**
	 * Check if current url is home.
	 *
	 * @param string $home_url home url.
	 * @param string $url url of current page.
	 * @return string
	 */
	public static function is_home( string $home_url, string $url ): string {

		// Prepare current url for segmentation.
		$modified_url = str_replace( '://', '', $url );

		// Check if modified url has segments.
		if ( false !== strpos( $modified_url, '/' ) ) {
			// Convert segments to array.
			$lang_code = explode( '/', $modified_url );

			// Get the second segment which may be a lang code.
			$lang_code = $lang_code[1];

			// Check if supposed lang code is actually a valid lang code: else return home url.
			if ( strlen( $lang_code ) < 2 || strlen( $lang_code ) > 7 ) {
				return $home_url;
			}

			/**
			 * Filters the active language with lang code.
			 *
			 * @since 3.11.4
			 *
			 * @param string  $empty_value NULL value.
			 * @param string  $lang_code language code.
			 */
			return apply_filters( 'wpml_language_is_active', null, $lang_code ) ? $url : $home_url; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}

		// Return home url if current url has no segments (is default home).
		return $home_url;
	}
}
