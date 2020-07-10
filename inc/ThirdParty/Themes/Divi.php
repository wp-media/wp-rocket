<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Divi implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$current_theme = wp_get_theme();

		if ( 'Divi' !== ( $current_theme->get( 'Name' ) || $current_theme->get( 'Template' ) ) ) {
			return [];
		}

		return [
			'rocket_exclude_js' => 'exclude_js',
		];
	}

	/**
	 * Excludes Divi's Salvatorre script from JS minification
	 *
	 * Exclude it to prevent an error after minification/concatenation
	 *
	 * @since 3.6.3
	 *
	 * @param array $excluded_js An array of JS paths to be excluded.
	 * @return array the updated array of paths
	 */
	public function exclude_js( $excluded_js ) {
		if ( ! defined( 'ET_BUILDER_URI' ) ) {
			return $excluded_js;
		}

		$excluded_js[] = str_replace( home_url(), '', ET_BUILDER_URI ) . '/scripts/salvattore.min.js';

		return $excluded_js;
	}
}
