<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
  * Allow to the purge the Varnish cache
  *
  * @since 2.6.8
  *
  * @param bool true will force the Varnish purge
 */
if ( apply_filters( 'do_rocket_varnish_http_purge', false ) || get_rocket_option( 'varnish_auto_purge', 0 ) ) :

/**
 * Purge all the domain
 *
 * @since 2.6.8
*/
add_action( 'before_rocket_clean_domain', '__rocket_varnish_clean_domain', 10, 3 );
function __rocket_varnish_clean_domain( $root, $lang, $url ) {
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge a specific page
 *
 * @since 2.6.8
*/
add_action( 'before_rocket_clean_file', '__rocket_varnish_clean_file' );
function __rocket_varnish_clean_file( $url ) {
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge the homepage and its pagination
 *
 * @since 2.6.8
*/
add_action( 'before_rocket_clean_home', '__rocket_varnish_clean_home', 10, 2 );
function __rocket_varnish_clean_home( $root, $lang ) {
	$home_url = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';
	
	rocket_varnish_http_purge( $home_url );	
	rocket_varnish_http_purge( $home_pagination_url );
}

endif;