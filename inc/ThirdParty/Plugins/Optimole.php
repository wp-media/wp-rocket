<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Optimole implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];

		if ( rocket_has_constant( 'OPTML_VERSION' ) ) {
			$events['wpmedia_plugin_family_show_imagify_banner'] = 'return_false';
		}

		return $events;
	}
}
