<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Subscriber for compatibility with the Events Calendar.
 */
class TheEventsCalendar implements Subscriber_Interface, PluginCompatibilityInterface {

	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return (bool) rocket_get_constant( 'TRIBE_EVENTS_FILE', false );
	}

	/**
	 * Subscribed events for The Events Calendar.
	 */
	public static function get_subscribed_events() {
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
