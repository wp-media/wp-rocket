<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Purge all the domain
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 * @param string $url  The home url.
 */
function rocket_varnish_clean_domain( $root, $lang, $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_domain()' );
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge a specific page
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $url The url to purge.
 */
function rocket_varnish_clean_file( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_file()' );
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge the homepage and its pagination
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 */
function rocket_varnish_clean_home( $root, $lang ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_home()' );
	$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';

	rocket_varnish_http_purge( $home_url );
	rocket_varnish_http_purge( $home_pagination_url );
}

/**
 * Sets the Varnish IP to localhost if Cloudflare is active
 *
 * @since 3.3.5
 * @deprecated 3.5
 * @author Remy Perona
 *
 * @return string
 */
function rocket_varnish_proxy_host() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::set_varnish_localhost()' );
	return 'localhost';
}

/**
 * Sets the Host header to the website domain if Cloudflare is active
 *
 * @since 3.3.5
 * @deprecated 3.5
 * @author Remy Perona
 *
 * @return string
 */
function rocket_varnish_proxy_request_host() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::set_varnish_purge_request_host()' );
	return wp_parse_url( home_url(), PHP_URL_HOST );
}

/**
 * Send data to Varnish
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param  string $url The URL to purge.
 * @return void
 */
function rocket_varnish_http_purge( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Addons\Varnish\Varnish::purge()' );
	$parse_url = get_rocket_parse_url( $url );

	$varnish_x_purgemethod = 'default';
	$regex                 = '';

	if ( 'vregex' === $parse_url['query'] ) {
		$varnish_x_purgemethod = 'regex';
		$regex                 = '.*';
	}

	/**
	 * Filter the Varnish IP to call
	 *
	 * @since 2.6.8
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
	 * @param string The HTTP protocol
	 */
	$scheme = apply_filters( 'rocket_varnish_http_purge_scheme', 'http' );

	$parse_url['host'] = ( $varnish_ip ) ? $varnish_ip : $parse_url['host'];
	$purgeme           = $scheme . '://' . $parse_url['host'] . $parse_url['path'] . $regex;

	wp_remote_request(
		$purgeme,
		array(
			'method'      => 'PURGE',
			'blocking'    => false,
			'redirection' => 0,
			/**
			 * Filters the headers to send with the Varnish purge request
			 *
			 * @since 3.1
			 * @author Remy Perona
			 *
			 * @param array $headers Headers to send.
			 */
			'headers'     => apply_filters(
				'rocket_varnish_purge_headers',
				[
					/**
					 * Filters the host value passed in the request headers
					 *
					 * @since 2.8.15
					 * @param string The host
					 */
					'host'           => apply_filters( 'rocket_varnish_purge_request_host', $parse_url['host'] ),
					'X-Purge-Method' => $varnish_x_purgemethod,
				]
			),
		)
	);
}
