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
