<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class WpDiscuz implements Subscriber_Interface {
	/**
	 * Subscriber for wpDiscuz.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'WPDISCUZ_DIR_NAME' ) ) {
			return [];
		}

		return [ 'option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => 'force_mobile_cache' ];
	}

	/**
	 * Forces the mobile cache when the plugin is enabled.
	 *
	 * @param array $options WP Rocket configs.
	 *
	 * @return array
	 */
	public function force_mobile_cache( $options ) {
		$options['do_caching_mobile_files'] = true;
		return $options;
	}
}
