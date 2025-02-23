<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.19
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [];
	}
}
