<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility class for WPX Cloud.
 */
class WPXCloud extends AbstractNoCacheHost {
	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_varnish_ip'                       => 'varnish_ip',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'do_rocket_varnish_http_purge'            => 'return_true',
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_htaccess_mod_expires'             => [ 'remove_htaccess_html_expire', 5 ],
		];
	}

	/**
	 * Adds WPX Cloud Varnish IP to varnish IPs array
	 *
	 * @param array $varnish_ip Varnish IP.
	 * @return array
	 */
	public function varnish_ip( $varnish_ip ) {
		if ( ! is_array( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		$varnish_ip[] = '127.0.0.1:6081';

		return $varnish_ip;
	}

	/**
	 * Remove expiration on HTML to prevent issue with Varnish cache.
	 *
	 * @param  string $rules htaccess rules.
	 * @return string Updated htaccess rules.
	 */
	public function remove_htaccess_html_expire( $rules ) {
		$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
		$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

		return $rules;
	}

	/**
	 * Displays custom title for the Varnish add-on
	 *
	 * @param array $settings Array of settings for Varnish.
	 * @return array
	 */
	public function varnish_addon_title( array $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
			// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'WPX'
		);

		return $settings;
	}
}
