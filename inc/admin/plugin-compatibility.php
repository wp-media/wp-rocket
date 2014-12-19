<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with an usual NGINX configuration which include 
 * try_files $uri $uri/ /index.php?q=$uri&$args
 *
 * @since 2.3.9
 */
add_filter( 'rocket_cache_query_strings', '__rocket_better_nginx_compatibility' );
function __rocket_better_nginx_compatibility( $query_strings ) {
	global $is_nginx;
	
	if ( $is_nginx ) {
		$query_strings[] = 'q';
	}
	
	return $query_strings;
}