<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Polygone implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_polygone() ) {
			return [];
		}

		return [
			'rocket_rucss_inline_content_exclusions' => 'add_rucss_content_excluded',
		];
	}

	/**
	 * Add excluded elements to rocket_rucss_safelist filter.
	 *
	 * @param array $excluded excluded elements.
	 * @return array
	 */
	public function add_rucss_content_excluded( $excluded ) {
		$excluded [] = '.expanding_bar_';
		return $excluded;
	}

	/**
	 * Checks if the current theme (or parent) is Polygone
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_polygone( $theme = null ) {
		$theme = $theme instanceof WP_Theme ? $theme : wp_get_theme();
		return ( 'Polygone' === $theme->get( 'Name' ) || 'polygone' === $theme->get_template() );
	}
}
