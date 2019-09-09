<?php
namespace WP_Rocket\Subscriber\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;

class RocketCDNSubscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [];
	}
}
