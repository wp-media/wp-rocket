<?php

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility with an usual NGINX configuration which include:
 *      try_files $uri $uri/ /index.php?q=$uri&$args
 *
 * @since 2.3.9
 *
 * @param array $query_strings Array of query strings to cache.
 *
 * @return array Updated array of query strings.
 */
function rocket_better_nginx_compatibility( $query_strings ) {
	global $is_nginx;

	if ( $is_nginx ) {
		$query_strings[] = 'q';
	}

	return $query_strings;
}
add_filter( 'rocket_cache_query_strings', 'rocket_better_nginx_compatibility' );
