<?php
/**
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 * @param  string $url The URL to parse.
 * @param  array  $zone (default: array( 'all' )). Deprecated.
 * @return string
 */
function get_rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	$container = apply_filters( 'rocket_container', '' );
	$cdn       = $container->get( 'cdn' );

	return $cdn->rewrite_url( $url );
}

/**
 * Wrapper of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 *
 * @param string $url The URL to parse.
 * @param array  $zone (default: array( 'all' )). Deprecated.
 */
function rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	echo esc_url( get_rocket_cdn_url( $url, $zone ) );
}

/**
 * Get all CNAMES.
 *
 * @since 2.1
 * @since 3.0 Don't check for WP Rocket CDN option activated to be able to use the function on Hosting with CDN auto-enabled.
 *
 * @param  string $zone List of zones. Default is 'all'.
 * @return array        List of CNAMES
 */
function get_rocket_cdn_cnames( $zone = 'all' ) {
	$hosts  = [];
	$cnames = get_rocket_option( 'cdn_cnames', [] );

	if ( $cnames ) {
		$cnames_zone = get_rocket_option( 'cdn_zone', [] );
		$zone        = (array) $zone;

		foreach ( $cnames as $k => $_urls ) {
			if ( ! in_array( $cnames_zone[ $k ], $zone, true ) ) {
				continue;
			}

			$_urls = explode( ',', $_urls );
			$_urls = array_map( 'trim', $_urls );

			foreach ( $_urls as $url ) {
				$hosts[] = $url;
			}
		}
	}

	/**
	 * Filter all CNAMES.
	 *
	 * @since 2.7
	 *
	 * @param array $hosts List of CNAMES.
	 */
	$hosts = (array) apply_filters( 'rocket_cdn_cnames', $hosts );
	$hosts = array_filter( $hosts );
	$hosts = array_flip( array_flip( $hosts ) );
	$hosts = array_values( $hosts );

	return $hosts;
}