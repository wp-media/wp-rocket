<?php

namespace WP_Rocket\ThirdParty;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Null Subscriber.
 *
 * Provides a base class to extend for common Subscribers.
 *
 * @since 3.6.3
 */
class NullSubscriber implements Subscriber_Interface {

	/**
	 * Get an array of subscribed events.
	 *
	 * To be overloaded with actual subscribed events in extending Subscribers.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [];
	}
}
