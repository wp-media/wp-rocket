<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Create a cache busting file with the version in the filename
 *
 * @since 2.9 Rework the function for better cache busting
 * @since 2.0 Code improvment: "ver=$wp_version" can be at any place now
 * @since 1.1.6
 *
 * @param string $src CSS/JS file URL
 * @return string updated CSS/JS file URL
 */
add_filter( 'script_loader_src', 'rocket_delete_script_wp_version', 15 );
add_filter( 'style_loader_src', 'rocket_delete_script_wp_version', 15 );
function rocket_delete_script_wp_version( $src ) {
	global $pagenow;
	
	if ( 'wp-login.php' == $pagenow ) {
    	return $src;
    }

    $full_src = rocket_add_url_protocol( $src );
    $new_path = str_replace( home_url( '/' ), '', $full_src );
    $new_path = str_replace( '/', '-', $new_path );
    $new_path = preg_replace( '/\.(js|css)\?ver=(.+)$/', '-$2.$1', rtrim( $new_path ) );

    $cache_busting_path = WP_ROCKET_CACHE_BUSTING_PATH . $new_path;
    $cache_busting_url  = WP_ROCKET_CACHE_BUSTING_URL . $new_path;

    if ( file_exists( $cache_busting_path ) && is_readable( $cache_busting_path ) ) {
    	return $cache_busting_url;
    }

    $response = wp_remote_get( $full_src );

    if ( ! is_array( $response ) || is_wp_error(  $response ) ) {
        return $src;
    }

    rocket_put_content( $cache_busting_path, $response['body'] );

    return $cache_busting_url;
}