<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Godaddy implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @since 3.9.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_varnish_field_settings'           => 'godaddy_varnish_field',
			'rocket_display_input_varnish_auto_purge' => [ 'return_false' ],
			'set_rocket_wp_cache_define'              => [ 'return_true' ],
			'rocket_cache_mandatory_cookies'          => [ 'return_empty_array', PHP_INT_MAX ],
			'rocket_htaccess_mod_rewrite'             => [ 'return_false' ],
			'rocket_htaccess_mod_expires'             => [ 'remove_html_expire_goddady', 5 ],
			'before_rocket_clean_domain'              => 'clean_domain_godaddy',
			'before_rocket_clean_file'                => 'clean_file_godaddy',
			'before_rocket_clean_home'                => [ 'clean_home_godaddy', 10, 2 ],
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.9.1
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function godaddy_varnish_field( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'GoDaddy'
		);

		return $settings;
	}

	/**
	 * Remove expiration on HTML to prevent issue with Varnish cache.
	 *
	 * @since 3.9.1
	 *
	 * @param  string $rules htaccess rules.
	 * @return string        Updated htaccess rules.
	 */
	public function remove_html_expire_goddady( $rules ) {
		$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
		$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

		return $rules;
	}

	/**
	 * Call the Varnish server to purge the cache with GoDaddy.
	 *
	 * @since 3.9.1
	 *
	 * @return void
	 */
	public function clean_domain_godaddy() {
		$this->godaddy_request( 'BAN' );
	}

	/**
	 * Call the Varnish server to purge a specific URL with GoDaddy.
	 *
	 * @since 3.9.1
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	public function clean_file_godaddy( $url ) {
		$this->godaddy_request( 'PURGE', home_url( $url ) );
	}

	/**
	 * Call the Varnish server to purge the home with GoDaddy.
	 *
	 * @since 3.9.1
	 *
	 * @param string $root root URL.
	 * @param string $lang language code.
	 * @return void
	 */
	public function clean_home_godaddy( $root, $lang ) {
		$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

		$this->godaddy_request( 'PURGE', $home_url );
		$this->godaddy_request( 'PURGE', $home_pagination_url );
	}


	/**
	 * Perform the call to the Varnish server to purge
	 *
	 * @since 2.9.5
	 * @source WPaaS\Cache
	 *
	 * @param string $method can be BAN or PURGE.
	 * @param string $url URL to purge.
	 * @return void
	 */
	private function godaddy_request( $method, $url = null ) {
		if ( ! method_exists( '\WPaas\Plugin', 'vip' ) ) {
			return;
		}

		if ( empty( $url ) ) {
			$url = home_url();
		}
		var_dump($url);
		$host = rocket_extract_url_component( $url, PHP_URL_HOST );
		var_dump('sssssss');
		var_dump($host);
		$url  = set_url_scheme( str_replace( $host, \WPaas\Plugin::vip(), $url ), 'http' );

		wp_cache_flush();

		// This forces the APC cache to flush across the server.
		update_option( 'gd_system_last_cache_flush', time() );

		wp_remote_request(
			esc_url_raw( $url ),
			[
				'method'   => $method,
				'blocking' => false,
				'headers'  => [
					'Host' => $host,
				],
			]
		);
	}

}
