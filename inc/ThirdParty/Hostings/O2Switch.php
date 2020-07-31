<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\NullSubscriber;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility class for O2Switch
 *
 * @since 3.6.3
 */
class O2Switch extends NullSubscriber implements Subscriber_Interface, ActivationInterface {
	use ReturnTypesTrait;

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_varnish_http_purge'            => 'return_true',
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_htaccess_mod_expires'             => [ 'remove_htaccess_html_expire', 5 ],
			'rocket_varnish_purge_headers'            => 'add_purge_headers',
			'rocket_varnish_purge_url'                => [ 'remove_regex_from_purge_url', 10, 2 ],
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
	public function varnish_addon_title( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'O2Switch'
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
	public function remove_htaccess_html_expire( $rules ) {
		$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
		$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

		return $rules;
	}

	/**
	 * Adjust purge request header array.
	 *
	 * @since 3.6.3
	 *
	 * @param array $headers Headers to send.
	 *
	 * @return array Array for headers to be sent.
	 */
	public function add_purge_headers( $headers ) {
		$headers['X-VC-Purge-Key'] = rocket_get_constant( 'O2SWITCH_VARNISH_PURGE_KEY' );

		if ( isset( $headers['X-Purge-Method'] ) && 'regex' === $headers['X-Purge-Method'] ) {
			$headers['X-Purge-Regex'] = '.*';

			unset( $headers['X-Purge-Method'] );
		}

		return $headers;
	}

	/**
	 * Remove regex part from purge_url as it's handled via headers instead.
	 *
	 * @since 3.6.3
	 *
	 * @param string $full_purge_url Full url for purge, regex included.
	 * @param string $main_purge_url Main url without regex.
	 *
	 * @return mixed
	 */
	public function remove_regex_from_purge_url( $full_purge_url, $main_purge_url ) {
		return $main_purge_url;
	}

	/**
	 * Performs these actions during the plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'activate_no_htaccess_html_expire' ] );
	}

	/**
	 * Remove expiration on HTML on activation to prevent issue with Varnish cache.
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function activate_no_htaccess_html_expire() {
		add_filter( 'rocket_htaccess_mod_expires', [ $this, 'remove_htaccess_html_expire' ] );
	}
}
