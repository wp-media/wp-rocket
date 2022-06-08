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
			'rocket_rucss_is_home_url'                     => [ 'is_secondary_home', 10, 2 ],
		];

		return $events;
	}

	/**
	 * Checks if page to be processed is secondary homepage.
	 *
	 * @param string $home_url home url.
	 * @param string $url url of current page.
	 * @return string
	 */
	public function is_secondary_home( string $home_url, string $url ): string {
		global $sitepress;

		// Get active languages on site.
		$languages = $sitepress->get_active_languages();

		foreach ( $languages as $lang ) {
			$lang_url = $sitepress->language_url( $lang['code'] );

			// Check if current url is a secondary homepage.
			if ( untrailingslashit( $lang_url ) !== $url ) {
				continue;
			}

			return $url;
		}

		return $home_url;
	}
}
