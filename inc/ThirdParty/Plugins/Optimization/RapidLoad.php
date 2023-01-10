<?php

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class RapidLoad implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'UUCSS_VERSION' ) ) {
			return [];
		}

		return [
			'rocket_maybe_disable_rucss'              => 'disable_rucss_option',
			'pre_get_rocket_option_remove_unused_css' => 'return_false',
		];
	}

	/**
	 * Disable RUCSS option.
	 *
	 * @param array $status RUCSS option status.
	 * @return array
	 */
	public function disable_rucss_option( array $status ): array {
		return [
			'disable' => true,
			'text'    => __( 'Removing Unused CSS is currently activated in Autoptimize\'s RapidLoad. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in Autoptimize\'s RapidLoad.', 'rocket' ),
		];
	}
}
