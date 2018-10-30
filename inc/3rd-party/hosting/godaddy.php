<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'WPaaS\Plugin' ) ) :
	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 */
	function rocket_godaddy_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'GoDaddy' );

		return $settings;
	}
	add_filter( 'rocket_varnish_field_settings', 'rocket_godaddy_varnish_field' );

	add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );

	add_filter( 'set_rocket_wp_cache_define', '__return_true' );

	/**
	 * Remove expiration on HTML to prevent issue with Varnish cache
	 *
	 * @since 2.9.5
	 * @author Remy Perona
	 *
	 * @param string $rules htaccess rules.
	 * @return Updated htaccess rules
	 */
	function rocket_remove_html_expire_goddady( $rules ) {
		$rules = <<<HTACCESS
# Expires headers (for better cache control)
<IfModule mod_expires.c>
	ExpiresActive on
	# Perhaps better to whitelist expires rules? Perhaps.
	ExpiresDefault                          "access plus 1 month"
	# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)
	ExpiresByType text/cache-manifest       "access plus 0 seconds"
	# Data
	ExpiresByType text/xml                  "access plus 0 seconds"
	ExpiresByType application/xml           "access plus 0 seconds"
	ExpiresByType application/json          "access plus 0 seconds"
	# Feed
	ExpiresByType application/rss+xml       "access plus 1 hour"
	ExpiresByType application/atom+xml      "access plus 1 hour"
	# Favicon (cannot be renamed)
	ExpiresByType image/x-icon              "access plus 1 week"
	# Media: images, video, audio
	ExpiresByType image/gif                 "access plus 1 month"
	ExpiresByType image/png                 "access plus 1 month"
	ExpiresByType image/jpeg                "access plus 1 month"
	ExpiresByType video/ogg                 "access plus 1 month"
	ExpiresByType audio/ogg                 "access plus 1 month"
	ExpiresByType video/mp4                 "access plus 1 month"
	ExpiresByType video/webm                "access plus 1 month"
	# HTC files  (css3pie)
	ExpiresByType text/x-component          "access plus 1 month"
	# Webfonts
	ExpiresByType application/x-font-ttf    "access plus 1 month"
	ExpiresByType font/opentype             "access plus 1 month"
	ExpiresByType application/x-font-woff   "access plus 1 month"
	ExpiresByType application/x-font-woff2  "access plus 1 month"
	ExpiresByType image/svg+xml             "access plus 1 month"
	ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
	# CSS and JavaScript
	ExpiresByType text/css                  "access plus 1 year"
	ExpiresByType application/javascript    "access plus 1 year"
</IfModule>

HTACCESS;

		return $rules;
	}
	add_filter( 'rocket_htaccess_mod_expires', 'rocket_remove_html_expire_goddady' );

	/**
	 * Call the Varnish server to purge the cache with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @return void
	 */
	function rocket_clean_domain_godaddy() {
		rocket_godaddy_request( 'BAN' );
	}
	add_action( 'before_rocket_clean_domain', 'rocket_clean_domain_godaddy' );

	/**
	 * Call the Varnish server to purge a specific URL with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	function rocket_clean_file_godaddy( $url ) {
		rocket_godaddy_request( 'PURGE', home_url( $url ) );
	}
	add_action( 'before_rocket_clean_file', 'rocket_clean_file_godaddy' );

	/**
	 * Call the Varnish server to purge the home with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @param string $root root URL.
	 * @param string $lang language code.
	 * @return void
	 */
	function rocket_clean_home_godaddy( $root, $lang ) {
		$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

		rocket_godaddy_request( 'PURGE', $home_url );
		rocket_godaddy_request( 'PURGE', $home_pagination_url );
	}
	add_action( 'before_rocket_clean_home', 'rocket_clean_home_godaddy', 10, 2 );

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
	function rocket_godaddy_request( $method, $url = null ) {
		$url  = empty( $url ) ? home_url() : $url;
		$host = rocket_extract_url_component( $url, PHP_URL_HOST );
		$url  = set_url_scheme( str_replace( $host, WPaas\Plugin::vip(), $url ), 'http' );

		wp_cache_flush();

		// This forces the APC cache to flush across the server.
		update_option( 'gd_system_last_cache_flush', time() );

		wp_remote_request(
			esc_url_raw( $url ),
			array(
				'method'   => $method,
				'blocking' => false,
				'headers'  => array(
					'Host' => $host,
				),
			)
		);
	}
endif;
