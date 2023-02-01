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
			'rocket_disable_rucss_setting'            => 'disable_rucss_setting',
			'pre_get_rocket_option_remove_unused_css' => 'maybe_disable_rucss',
			'deactivated_plugin'                      => [ 'rocket_clean_cache_on_deactivation', 12 ],
		];
	}

	/**
	 * Disable RUCSS setting.
	 *
	 * @param array $status RUCSS option status.
	 * @return array
	 */
	public function disable_rucss_setting( array $status ): array {
		if ( ! $this->is_rapidload_active() ) {
			return $status;
		}

		return [
			'disable' => true,
			'text'    => __( 'Automated unused CSS removal is currently activated in RapidLoad Power-Up for Autoptimize. If you want to use WP Rocket\'s Remove Unused CSS feature, disable the  RapidLoad Power-Up for Autoptimize plugin.', 'rocket' ),
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
	 * Clean WP Rocket Cache when Rapidload is deactivated.
	 *
	 * @param string $plugin Plugin file.
	 * @return void
	 */
	public function rocket_clean_cache_on_deactivation( string $plugin ): void {
		if ( ! $this->is_rapidload_active() ) {
			return;
		}

		if ( 'unusedcss/unusedcss.php' !== $plugin ) {
			return;
		}

		rocket_dismiss_box( 'rocket_warning_plugin_modification' );
		rocket_clean_domain();
	}

	/**
	 * Check if RapidLoad Power-Up for Autoptimize is active.
	 *
	 * @return boolean
	 */
	private function is_rapidload_active(): bool {
		$autoptimize_uucss_settings = get_option( 'autoptimize_uucss_settings' );

		return ( isset( $autoptimize_uucss_settings['uucss_api_key_verified'] ) && 1 === $autoptimize_uucss_settings['uucss_api_key_verified'] && $autoptimize_uucss_settings['valid_domain'] );
	}
}
