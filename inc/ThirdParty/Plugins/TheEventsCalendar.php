<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with the Events Calendar.
 */
class TheEventsCalendar implements Subscriber_Interface {


	/**
	 * Subscribed events for The Events Calendar.
	 */
	public static function get_subscribed_events() {
		if ( ! rocket_get_constant( 'TRIBE_EVENTS_FILE', false ) ) {
			return [];
		}

		return [
			'rocket_preload_exclude_urls' => 'exclude_from_preload_calendars',
		];
	}

	/**
	 * Exclude calendars from the preload.
	 *
	 * @param array $excluded excluded urls.
	 * @return array
	 */
	public function exclude_from_preload_calendars( $excluded ) {

		if ( ! function_exists( 'tribe_get_option' ) ) {
			return $excluded;
		}

		$uri = tribe_get_option( 'eventsSlug', 'event' );

		$excluded[] = "/$uri/20(.*)";

		return $excluded;
	}
}
