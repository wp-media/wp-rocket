<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Array of events to listen to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [];
	}
}
