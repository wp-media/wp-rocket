<?php

defined( 'ABSPATH' ) || exit;

/**
 * Indicate to bypass rocket optimizations.
 *
 * Checks for "nowprocket" query string in the url to bypass rocket processes.
 *
 * @since 3.7
 *
 * @return bool True to indicate should bypass; false otherwise.
 */
function rocket_bypass() {
	global $wp;

	$url = wp_parse_url( add_query_arg( $wp->query_vars, home_url( $wp->request ) ) );

	return isset( $url['query'] ) && false !== strpos( $url['query'], 'nowprocket' );
}
