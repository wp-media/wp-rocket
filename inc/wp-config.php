<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Added or set the value of the WP_CACHE constant
 *
 * @since 2.0.0
 *
 */
 
function set_rocket_wp_cache_define( $enable = true ) 
{
	// If WP_CACHE is already define, return to get a coffee
	if( $enable && defined( 'WP_CACHE' ) && WP_CACHE  )
		return;
	
	// Get content of the config file
	$config_file = @file_get_contents( ABSPATH . 'wp-config.php' );
     
    if ( !$config_file )
        return;
	
	// Get the value of WP_CACHE constant
	$enable = $enable ? 'true' : 'false';
	
	// Get the content of the WP_CACHE constant added by WP Rocket
	$define = "/** Enable Cache */\r\n" . "define('WP_CACHE', $enable); // Added by WP Rocket\r\n";
	
	$config_file = preg_replace( "~\\/\\*\\* Enable Cache \\*\\*?\\/.*?\\/\\/ Added by WP Rocket(\r\n)*~s", '', $config_file );
    $config_file = preg_replace( "~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?WP_CACHE['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is", '', $config_file );
	$config_file = preg_replace( '~<\?(php)?~', "\\0\r\n" . $define, $config_file );
	
	rocket_put_content( ABSPATH . 'wp-config.php', $config_file );
}