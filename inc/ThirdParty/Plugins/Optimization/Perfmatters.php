<?php

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Perfmatters implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'PERFMATTERS_VERSION' ) ) {
			return [];
		}

		return [
			'rocket_maybe_disable_rucss' => 'disable_rucss_option',
		];
	}

	/**
	 * Disable RUCSS option.
	 *
	 * @return array
	 */
	public function disable_rucss_option(): array {
		$perfmatters_options = get_option( 'perfmatters_options' );

		if ( empty( $perfmatters_options['assets']['remove_unused_css'] ) ) {
			return [];
		}

		return [
			'disable' => true,
			'text'    => __( 'Removing Unused CSS is currently activated in Perfmatters. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in Perfmatters.', 'rocket' ),
		];
	}
}
