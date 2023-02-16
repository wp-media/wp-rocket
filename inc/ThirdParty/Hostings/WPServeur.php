<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\ThirdParty\ReturnTypesTrait;

class WPServeur extends AbstractNoCacheHost
{
	use ReturnTypesTrait;

	public static function get_subscribed_events()
	{
		return [
			'do_rocket_varnish_http_purge' => 'return_true',
			'rocket_cache_mandatory_cookies' => ['return_empty_array',PHP_INT_MAX],
			'rocket_varnish_field_settings' => 'wpserveur_varnish_field',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since  3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	function wpserveur_varnish_field( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'WP Serveur'
		);

		return $settings;
	}
}
