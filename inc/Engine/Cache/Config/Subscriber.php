<?php
namespace WP_Rocket\Engine\Cache\Config;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the Cache Config
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'permalink_structure_changed' => 'regenerate_config_file',
		];
	}

	/**
	 * Regenerate config file.
	 *
	 * @return void
	 */
	public function regenerate_config_file() {
		rocket_generate_config_file();
	}
}
