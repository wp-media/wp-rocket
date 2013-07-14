<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Remove query strings from static files if ?ver= egal WordPress version
 *
 * since 1.1.6
 *
 */
  
add_filter( 'script_loader_src', 'rocket_delete_script_wp_version', 15 );
add_filter( 'style_loader_src', 'rocket_delete_script_wp_version', 15 );
function rocket_delete_script_wp_version( $src )
{
	global $wp_version;  
	$parts = explode( '?', $src );
	
	if( $parts[1] == 'ver='.$wp_version )
		return $parts[0];
	
	return $src;
   
}