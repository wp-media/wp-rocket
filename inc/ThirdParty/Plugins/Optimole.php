<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Optimole implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];

		if ( rocket_has_constant( 'OPTML_VERSION' ) ) {
			$events['wpmedia_plugin_family_show_imagify_banner'] = 'hide_imagify_banner';
		}

		return $events;
	}

	/**
	 * Hides the Imagify banner when Optimole is enabled
	 *
	 * @since 3.20.5
	 *
	 * @return false False to hide the banner.
	 */
	public function hide_imagify_banner() {
		return false;
	}
}
