<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Remove query strings from static files if ?ver= egal WordPress version
 *
 * @since 2.0 Code improvment: "ver=$wp_version" can be at any place now
 * @since 1.1.6
 */
add_filter( 'script_loader_src', 'rocket_delete_script_wp_version', 15 );
add_filter( 'style_loader_src', 'rocket_delete_script_wp_version', 15 );
function rocket_delete_script_wp_version( $src ) {
	global $pagenow;
	
	if ( 'wp-login.php' !== $pagenow ) {
		$src = rtrim( str_replace( array( 'ver='.$GLOBALS['wp_version'], '?&', '&&' ), array( '', '?', '&' ), $src ), '?&' );
	}
	
	return $src;
}