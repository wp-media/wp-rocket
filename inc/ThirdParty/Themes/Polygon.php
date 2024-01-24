<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Polygon implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_rucss_inline_content_exclusions' => 'add_rucss_content_excluded',
		];
	}

	/**
	 * Add excluded elements to rocket_rucss_inline_content_exclusions filter.
	 *
	 * @param array $excluded excluded elements.
	 * @return array
	 */
	public function add_rucss_content_excluded( $excluded ) {
		$excluded [] = '.expanding_bar_';
		return $excluded;
	}
}
