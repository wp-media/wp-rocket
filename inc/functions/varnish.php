<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

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
