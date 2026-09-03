<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Subscriber for compatibility with ConvertPlug.
 */
class ConvertPlug implements Subscriber_Interface, PluginCompatibilityInterface {

	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return (bool) rocket_get_constant( 'CP_VERSION' );
	}

	/**
	 * Subscriber for compatibility with ConvertPlug.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];

		$events['rocket_rucss_inline_atts_exclusions'] = 'excluded_from_rucss';

		return $events;
	}

	/**
	 * Exclude css from RUCSS.
	 *
	 * @param array $excluded excluded css.
	 * @return array
	 */
	public function excluded_from_rucss( $excluded ) {
		$excluded[] = 'cp-form-css';
		return $excluded;
	}
}
