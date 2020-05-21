<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for Cloudways Varnish
 *
 * @since 3.5.5
 */
class Cloudways implements Subscriber_Interface {
	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.5.5
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! isset( $_SERVER['cw_allowed_ip'] ) ) {
			return [];
		}

		return [
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'do_rocket_varnish_http_purge'            => 'return_true',
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_varnish_ip'                       => 'varnish_ip',
		];
	}

	/**
	 * Returns false
	 *
	 * @since 3.5.5
	 *
	 * @return bool
	 */
	public function return_false() {
		return false;
	}

	/**
	 * Returns true
	 *
	 * @since 3.5.5
	 *
	 * @return true
	 */
	public function return_true() {
		return true;
	}

	/**
	 * Displays custom title for the Varnish add-on
	 *
	 * @since 3.5.5
	 *
	 * @param array $settings Array of settings for Varnish.
	 * @return array
	 */
	public function varnish_addon_title( array $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
			// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'Cloudways'
		);

		return $settings;
	}

	/**
	 * Adds Cloudways Varnish IP to varnish IPs array
	 *
	 * @since 3.5.5
	 *
	 * @param array $varnish_ip Varnish IP.
	 * @return array
	 */
	public function varnish_ip( $varnish_ip ) {
		if ( ! is_array( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		$varnish_ip[] = '127.0.0.1:8080';

		return $varnish_ip;
	}
}
