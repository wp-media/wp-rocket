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
			'do_rocket_varnish_http_purge'            => 'should_purge',
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_varnish_ip'                       => 'varnish_ip',
		];
	}

	/**
	 * Determine if the Varnish server is up and running.
	 *
	 * @since 3.6.1
	 */
	private static function is_varnish_running() {
		if ( ! isset( $_SERVER['HTTP_X_VARNISH'] ) ) {
			return false;
		}

		if ( ! isset( $_SERVER['HTTP_X_APPLICATION'] ) ) {
			return false;
		}

		return ( 'varnishpass' !== trim( strtolower( $_SERVER['HTTP_X_APPLICATION'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
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
	 * Returns should purge Varnish.
	 *
	 * @since 3.5.5
	 *
	 * @return true
	 */
	public function should_purge() {
		return self::is_varnish_running();
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
		if ( ! self::is_varnish_running() ) {
			$settings['varnish_auto_purge']['title'] = sprintf(
				// Translators: %s = Hosting name.
				__( 'Varnish auto-purge will be automatically enabled once Varnish is enabled on your %s server.', 'rocket' ),
				'Cloudways'
			);

			return $settings;
		}
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
		if ( ! self::is_varnish_running() ) {
			return $varnish_ip;
		}
		if ( ! is_array( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		$varnish_ip[] = '127.0.0.1:8080';

		return $varnish_ip;
	}
}
