<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Remove query strings from static files if ?ver= egal WordPress version
 *
 * @since 2.0 : code improvment: "ver=$wp_version" can be at any place now
 * @since 1.1.6
 *
 */
  
add_filter( 'script_loader_src', 'rocket_delete_script_wp_version', 15 );
add_filter( 'style_loader_src', 'rocket_delete_script_wp_version', 15 );
function rocket_delete_script_wp_version( $src )
{
	return rtrim( str_replace( array( 'ver='.$GLOBALS['wp_version'], '?&', '&&' ), array( '', '?', '&' ), $src ), '?&' );
}