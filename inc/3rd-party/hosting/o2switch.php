<?php

defined( 'ABSPATH' ) || exit;

/**
 * Changes the text on the Varnish one-click block.
 *
 * @since 3.1
 * @author Remy Perona
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_o2switch_varnish_field( $settings ) {
	// Translators: %s = Hosting name.
	$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'o2switch' );

	return $settings;
}
add_filter( 'rocket_varnish_field_settings', 'rocket_o2switch_varnish_field' );

add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );
// Prevent mandatory cookies on hosting with server cache.
add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

/**
 * Purge all the domain.
 *
 * @since 3.1
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 * @param string $url  The home url.
 */
function rocket_o2switch_varnish_clean_domain( $root, $lang, $url ) {
	rocket_o2switch_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}
add_action( 'before_rocket_clean_domain', 'rocket_o2switch_varnish_clean_domain', 10, 3 );

/**
 * Purge a specific page.
 *
 * @since 3.1
 *
 * @param string $url The url to purge.
 */
function rocket_o2switch_varnish_clean_file( $url ) {
	rocket_o2switch_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}
add_action( 'before_rocket_clean_file', 'rocket_o2switch_varnish_clean_file' );

/**
 * Purge the homepage and its pagination.
 *
 * @since 3.1
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 */
function rocket_o2switch_varnish_clean_home( $root, $lang ) {
	$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';

	rocket_o2switch_varnish_http_purge( $home_url );
	rocket_o2switch_varnish_http_purge( $home_pagination_url );
}
add_action( 'before_rocket_clean_home', 'rocket_o2switch_varnish_clean_home', 10, 2 );

/**
 * Send data to Varnish.
 *
 * @since 3.1
 *
 * @param  string $url The URL to purge.
 */
function rocket_o2switch_varnish_http_purge( $url ) {
	$parse_url = get_rocket_parse_url( $url );

	// This filter is documented in inc/functions/varnish.php.
	$headers = apply_filters(
		'rocket_varnish_purge_headers',
		[
			/**
			 * Filters the host value passed in the request headers
			 *
			 * @since 2.8.15
			 * @param string The host
			 */
			'host'           => apply_filters( 'rocket_varnish_purge_request_host', $parse_url['host'] ),
			'X-VC-Purge-Key' => O2SWITCH_VARNISH_PURGE_KEY,
		]
	);

	if ( 'vregex' === $parse_url['query'] ) {
		$headers['X-Purge-Regex'] = '.*';
	}

	/**
	 * Filter the Varnish IP to call
	 *
	 * @since 2.6.8
	 *
	 * @param string The Varnish IP
	*/
	$varnish_ip = apply_filters( 'rocket_varnish_ip', '' );

	if ( defined( 'WP_ROCKET_VARNISH_IP' ) && ! $varnish_ip ) {
		$varnish_ip = WP_ROCKET_VARNISH_IP;
	}

	/**
	 * Filter the HTTP protocol (scheme)
	 *
	 * @since 2.7.3
	 *
	 * @param string The HTTP protocol
	 */
	$scheme = apply_filters( 'rocket_varnish_http_purge_scheme', $parse_url['scheme'] );

	$parse_url['host'] = ( $varnish_ip ) ? $varnish_ip : $parse_url['host'];
	$purgeme           = $scheme . '://' . $parse_url['host'] . $parse_url['path'];

	wp_remote_request(
		$purgeme,
		[
			'method'      => 'PURGE',
			'blocking'    => false,
			'redirection' => 0,
			'headers'     => $headers,
		]
	);
}

/**
 * Remove expiration on HTML to prevent issue with Varnish cache.
 *
 * @since 3.1
 * @author Remy Perona
 *
 * @param  string $rules htaccess rules.
 *
 * @return string        Updated htaccess rules.
 */
function rocket_o2switch_remove_html_expire( $rules ) {
	$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
	$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

	return $rules;
}
add_filter( 'rocket_htaccess_mod_expires', 'rocket_o2switch_remove_html_expire', 5 );
