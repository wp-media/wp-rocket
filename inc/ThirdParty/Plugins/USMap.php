<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class USMap implements Subscriber_Interface {


	/**
	 * Subscribed events for USMap.
	 *
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		$events = [];
		if ( defined( 'USMAP_VERSION' ) ) {
			$events['rocket_delay_js_exclusions'] = 'exclude_from_delay_js';
		}

		return $events;
	}

	/**
	 * Adds the config script from USMap to delay JS excluded files.
	 *
	 * @param  array $excluded List of excluded files.
	 * @return array        List of excluded files.
	 */
	public function exclude_from_delay_js( array $excluded = [] ) {
		$excluded[] = 'us_config';
		return $excluded;
	}
}
