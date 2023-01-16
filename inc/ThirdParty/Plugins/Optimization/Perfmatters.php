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
			'rocket_disable_rucss_setting'            => 'disable_rucss_setting',
			'pre_get_rocket_option_remove_unused_css' => 'maybe_disable_rucss',
		];
	}

	/**
	 * Disable RUCSS setting.
	 *
	 * @param array $status RUCSS option status.
	 * @return array
	 */
	public function disable_rucss_setting( array $status ): array {
		if ( ! $this->is_perfmatters_rucss_active() ) {
			return $status;
		}

		return [
			'disable' => true,
			'text'    => __( 'Remove Unused CSS is currently activated in Perfmatters. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in Perfmatters.', 'rocket' ),
		];
	}

	/**
	 * Disable RUCSS option.
	 *
	 * @return bool|null
	 */
	public function maybe_disable_rucss() {
		return $this->is_perfmatters_rucss_active() ? false : null;
	}

	/**
	 * Check if perfmatters rucss is active.
	 *
	 * @return bool
	 */
	private function is_perfmatters_rucss_active(): bool {
		$perfmatters_options = get_option( 'perfmatters_options' );

		return ! empty( $perfmatters_options['assets']['remove_unused_css'] );
	}
}
