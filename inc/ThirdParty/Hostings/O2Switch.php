<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility class for O2Switch
 *
 * @since 3.6.3
 */
class O2Switch implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! rocket_has_constant( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
			return [];
		}

		return [
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_htaccess_mod_expires'             => [ 'remove_htaccess_html_expire', 5 ],
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.6.3
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	function varnish_addon_title( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf(
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'o2switch'
		);

		return $settings;
	}

	/**
	 * Remove expiration on HTML to prevent issue with Varnish cache.
	 *
	 * @since 3.6.3
	 *
	 * @param  string $rules htaccess rules.
	 *
	 * @return string        Updated htaccess rules.
	 */
	function remove_htaccess_html_expire( $rules ) {
		$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
		$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

		return $rules;
	}

}
