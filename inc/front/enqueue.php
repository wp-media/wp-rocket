<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Remove query strings from static files if ?ver= egal WordPress version
 *
 * since 1.1.6
 * since 1.4.0 : code improvment: "ver=$wp_version" can be at any place now
 *
 */
  
add_filter( 'script_loader_src', 'rocket_delete_script_wp_version', 15 );
add_filter( 'style_loader_src', 'rocket_delete_script_wp_version', 15 );
function rocket_delete_script_wp_version( $src )
{
	return rtrim( str_replace( array( 'ver='.$GLOBALS['wp_version'], '?&', '&&' ), array( '', '?', '&' ), $src ), '?&' );
}