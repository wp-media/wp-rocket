<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Ads;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Adthrive implements Subscriber_Interface {
	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
	}
}
