<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class WpDiscuz implements Subscriber_Interface {

	use ReturnTypesTrait;

	/**
	 * Subscriber for wpDiscuz.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'WPDISCUZ_DIR_NAME' ) ) {
			return [];
		}

		return [
			'pre_get_rocket_option_do_caching_mobile_files' => 'return_true',
			'pre_get_rocket_option_cache_mobile'            => 'return_true',
		];
	}
}
