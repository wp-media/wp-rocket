<?php

namespace WP_Rocket\ThirdParty\Hostings;

/**
 * Compatibility class for DreamPress
 *
 * @since 3.7.2
 */
class Dreampress extends AbstractNoCacheHost {
	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.7.2
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_varnish_http_purge'            => 'return_true',
			'rocket_varnish_field_settings'           => 'set_varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_varnish_ip'                       => 'set_varnish_host',
			'rocket_set_wp_cache_constant'            => 'return_false',
			'do_rocket_generate_caching_files'        => 'return_false',
			'rocket_generate_advanced_cache_file'     => 'return_false',
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_htaccess_mod_expires'             => [ 'remove_htaccess_html_expire', 5 ],
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.7.2
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function set_varnish_addon_title( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'DreamPress'
		);

		return $settings;
	}

	/**
	 * Sets the Varnish host to localhost
	 *
	 * @since 3.7.2
	 *
	 * @param mixed $hosts Varnish hosts.
	 * @return array
	 */
	public function set_varnish_host( $hosts ) {
		if ( ! is_array( $hosts ) ) {
			$hosts = (array) $hosts;
		}

		if ( in_array( 'localhost', $hosts, true ) ) {
			return $hosts;
		}

		$hosts[] = 'localhost';

		return $hosts;
	}

	/**
	 * Remove expiration on HTML to prevent issue with Varnish cache.
	 *
	 * @since 3.7.2
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
	 * Performs these actions during the plugin activation
	 *
	 * @since 3.7.2
	 *
	 * @return void
	 */
	public function activate() {
		parent::activate();

		add_action( 'rocket_activation', [ $this, 'activate_no_htaccess_html_expire' ] );
	}

	/**
	 * Remove expiration on HTML on activation to prevent issue with Varnish cache.
	 *
	 * @since 3.7.2
	 *
	 * @return void
	 */
	public function activate_no_htaccess_html_expire() {
		add_filter( 'rocket_htaccess_mod_expires', [ $this, 'remove_htaccess_html_expire' ] );
	}
}
