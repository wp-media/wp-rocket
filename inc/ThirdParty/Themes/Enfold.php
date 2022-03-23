<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Enfold implements Subscriber_Interface {


	public static function get_subscribed_events() {
		$events = [];
		if ( function_exists( 'avia_lang_setup' ) ) {
			$events['rocket_delay_js_exclusions'] = 'exclude_js';
		}
		 return $events;
	}

	/**
	 * Excludes Enfold script from Delay JS
	 *
	 * Prevent an error after minification/concatenation
	 *
	 * @since 3.11.1
	 *
	 * @param array $excluded_js An array of JS paths to be excluded.
	 *
	 * @return array the updated array of paths
	 */
	public function exclude_js( $excluded_js ) {
		$excluded_js[] = '/jquery-migrate(.min)?.js';
		$excluded_js[] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';
		$excluded_js[] = 'var avia_is_mobile';
		$excluded_js[] = '/wp-content/uploads/dynamic_avia/avia-footer-scripts-(.*).js';

		return $excluded_js;
	}
}
