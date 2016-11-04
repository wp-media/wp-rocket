<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Create a cache busting file with the version in the filename
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $src CSS/JS file URL
 * @return string updated CSS/JS file URL
 */
add_filter( 'script_loader_src', 'rocket_browser_cache_busting', 15 );
add_filter( 'style_loader_src', 'rocket_browser_cache_busting', 15 );
function rocket_browser_cache_busting( $src ) {
	global $pagenow;

    if ( ! get_rocket_option( 'remove_query_strings' ) ) {
        return $src;
    }
	
	if ( 'wp-login.php' == $pagenow ) {
    	return $src;
    }

    $full_src = rocket_add_url_protocol( $src );

    if ( parse_url( $full_src, PHP_URL_HOST ) !== '' && strpos( $full_src, home_url() ) === false ) {
        return $src;
    } 
    
    $relative_src_path      = str_replace( home_url( '/' ), '', $full_src );
    $full_src_path          = ABSPATH . dirname( $relative_src_path );
    $cache_busting_filename = preg_replace( '/\.(js|css)\?ver=(.+)$/', '-$2.$1', rtrim( str_replace( '/', '-', $relative_src_path ) ) );
    
    $blog_id                = get_current_blog_id();
    $cache_busting_path     = WP_ROCKET_CACHE_BUSTING_PATH . $blog_id . '/';
    $cache_busting_filepath = $cache_busting_path . $cache_busting_filename;
    $cache_busting_url      = WP_ROCKET_CACHE_BUSTING_URL . $blog_id . '/' . $cache_busting_filename;

    if ( file_exists( $cache_busting_filepath ) && is_readable( $cache_busting_filepath ) ) {
    	return $cache_busting_url;
    }

    $response = wp_remote_get( $full_src );

    if ( ! is_array( $response ) || is_wp_error(  $response ) ) {
        return $src;
    }

    if ( current_filter() == 'style_loader_src' ) {
        if ( ! class_exists( 'Minify_CSS_UriRewriter' ) ) {
            require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/UriRewriter.php' );
        }
        // Rewrite import/url in CSS content to add the absolute path to the file
        $file_content = Minify_CSS_UriRewriter::rewrite( $response['body'], $full_src_path );
    } else {
        $file_content = $response['body'];
    }

    if ( ! is_dir( $cache_busting_path ) ) {
        rocket_mkdir_p( $cache_busting_path );
    }

    rocket_put_content( $cache_busting_filepath, $file_content );

    return $cache_busting_url;
}