<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

global $wp_version;

/**
 * Disable the emoji functionality to reduce then number of external HTTP requests. 
 *
 * @since 2.7
 */
if ( version_compare( $wp_version, '4.2' ) >= 0 && get_rocket_option( 'emoji', 0 ) ) :

add_action( 'init', '__rocket_disable_emoji' );
function __rocket_disable_emoji() {
	remove_action( 'wp_head'				, 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts'	, 'print_emoji_detection_script' );	
	remove_filter( 'the_content_feed'		, 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss'		, 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail'				, 'wp_staticize_emoji_for_email' );
}

/**
 * Remove the tinymce emoji plugin.
 * 
 * @since 2.7
 */
add_filter( 'tiny_mce_plugins', '__rocket_disable_emoji_tinymce' );
function __rocket_disable_emoji_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
	
	return array();
}

endif;