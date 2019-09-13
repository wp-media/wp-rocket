<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Purge all the domain
 *
 * @since 2.6.8
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 * @param string $url  The home url.
 */
function rocket_varnish_clean_domain( $root, $lang, $url ) {
    rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge a specific page
 *
 * @since 2.6.8
 *
 * @param string $url The url to purge.
 */
function rocket_varnish_clean_file( $url ) {
    rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge the homepage and its pagination
 *
 * @since 2.6.8
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 */
function rocket_varnish_clean_home( $root, $lang ) {
    $home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
    $home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';

    rocket_varnish_http_purge( $home_url );
    rocket_varnish_http_purge( $home_pagination_url );
}

if ( get_rocket_option( 'do_cloudflare' ) ) {
    /**
     * Sets the Varnish IP to localhost if Cloudflare is active
     *
     * @since 3.3.5
     * @author Remy Perona
     *
     * @return string
     */
    function rocket_varnish_proxy_host() {
        return 'localhost';
    }
    add_filter( 'rocket_varnish_ip', 'rocket_varnish_proxy_host', 9 );

    /**
     * Sets the Host header to the website domain if Cloudflare is active
     *
     * @since 3.3.5
     * @author Remy Perona
     *
     * @return string
     */
    function rocket_varnish_proxy_request_host() {
        return wp_parse_url( home_url(), PHP_URL_HOST );
    }
    add_filter( 'rocket_varnish_purge_request_host', 'rocket_varnish_proxy_request_host', 9 );
}

/**
 * Send data to Varnish
 *
 * @since 2.6.8
 *
 * @param  string $url The URL to purge.
 * @return void
 */
function rocket_varnish_http_purge( $url ) {
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