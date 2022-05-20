<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with ConvertPlug.
 */
class ConvertPlug implements Subscriber_Interface {


	/**
	 * Subscriber for compatibility with ConvertPlug.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];
		if ( ! self::isActivated() ) {
			return $events;
		}

		$events['rocket_rucss_inline_atts_exclusions'] = 'excluded_from_rucss';

		return $events;
	}

	/**
	 * Check if the plugin is activated.
	 *
	 * @return bool
	 */
	protected static function isActivated() {
		return defined( 'CP_VERSION' );
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
