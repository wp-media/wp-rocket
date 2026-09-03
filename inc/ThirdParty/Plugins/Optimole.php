<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Optimole implements Subscriber_Interface, PluginCompatibilityInterface {
	use ReturnTypesTrait;

	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return (bool) rocket_get_constant( 'OPTML_VERSION' );
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];

		$events['wpmedia_plugin_family_show_imagify_banner'] = 'return_false';

		return $events;
	}
}
