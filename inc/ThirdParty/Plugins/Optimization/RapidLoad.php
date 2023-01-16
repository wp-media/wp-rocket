<?php

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

class RapidLoad implements Subscriber_Interface {

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
			'rocket_disable_rucss_setting'              => 'disable_rucss_setting',
			'pre_get_rocket_option_remove_unused_css' => 'maybe_disable_rucss',
		];
	}

	/**
	 * Disable RUCSS option.
	 *
	 * @param array $status RUCSS option status.
	 * @return array
	 */
	public function disable_rucss_option( array $status ): array {
		if ( ! $this->is_rapidload_active() ) {
			return $status;
		}

		return [
			'disable' => true,
			'text'    => __( 'Automated unused CSS removal is currently activated in RapidLoad Power-Up for Autoptimize. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in RapidLoad Power-Up for Autoptimize.', 'rocket' ),
		];
	}

	/**
	 * Disable RUCSS option.
	 *
	 * @return bool|null
	 */
	public function maybe_disable_rucss() {
		return $this->is_rapidload_active() ? false : null;
	}

	/**
	 * Check if RapidLoad Power-Up for Autoptimize is active.
	 *
	 * @return boolean
	 */
	private function is_rapidload_active(): bool {
		$autoptimize_uucss_settings = get_option( 'autoptimize_uucss_settings' );

		return ( isset( $autoptimize_uucss_settings['uucss_api_key_verified'] ) && 1 === $autoptimize_uucss_settings['uucss_api_key_verified'] );
	}
}
