<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Create a cache busting file with the version in the filename
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $src CSS/JS file URL.
 * @param string $current_filter Current WordPress filter.
 * @return string updated CSS/JS file URL
 */
if ( ! get_rocket_option( 'minify_css' ) ) {
    add_filter( 'style_loader_src', 'rocket_browser_cache_busting', 15 );
}

if ( ! get_rocket_option( 'minify_js' ) ) {
    add_filter( 'script_loader_src', 'rocket_browser_cache_busting', 15 );
}

function rocket_browser_cache_busting( $src, $current_filter = '' ) {
	global $pagenow;

    if ( ! get_rocket_option( 'remove_query_strings' ) ) {
        return $src;
    }
	
	if ( 'wp-login.php' == $pagenow ) {
    	return $src;
    }

    if ( false === strpos( $src, '.css' ) && false === strpos( $src, '.js' ) ) {
        return $src;
    }
 
	if ( empty( $current_filter ) ) {
		$current_filter = current_filter();
	}

    if ( 'script_loader_src' === $current_filter ) {
        $deferred_js_files = get_rocket_deferred_js_files();

        if ( (bool) $deferred_js_files ) {
            $deferred_js_files = array_flip( $deferred_js_files );
            $clean_src         = strtok( $src, '?' );
            if ( isset( $deferred_js_files[ $clean_src ] ) ) {
                return $src;
            }
        }
    }

	$full_src = ( substr( $src, 0, 2 ) === '//' ) ? rocket_add_url_protocol( $src ) : $src;

	switch ( $current_filter ) {
		case 'script_loader_src':
			$extension = 'js';
			break;
		case 'style_loader_src':
			$extension = 'css';
			break;
	}
    
    $hosts 		 = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
    $hosts[] 	 = parse_url( home_url(), PHP_URL_HOST );
    $hosts_index = array_flip( $hosts );
    list( $file_host, $relative_src_path ) = get_rocket_parse_url( $full_src );

	if ( $file_host == '' ) {
        $full_src = home_url() . $src;
    }

    if ( false !== strpos( $full_src, '://' ) && ! isset( $hosts_index[ $file_host ] ) ) {
        return $src;
    }

    $relative_src_path      = ltrim( $relative_src_path, '/' );
    $full_src_path          = ABSPATH . dirname( $relative_src_path );
    /*
     * Filters the cache busting filename
     *
     * @since 2.9
     * @author Remy Perona
     *
     * @param string $filename filename for the cache busting file
     */
    $cache_busting_filename = apply_filters( 'rocket_cache_busting_filename', preg_replace( '/\.(js|css)\?ver=(.+)$/', '-$2.$1', rtrim( str_replace( '/', '-', $relative_src_path ) ) ) );
    $cache_busting_paths    = rocket_get_cache_busting_paths( $cache_busting_filename, $extension );

    if ( file_exists( $cache_busting_paths['filepath'] ) && is_readable( $cache_busting_paths['filepath'] ) ) {
    	return $cache_busting_paths['url'];
    }

    $response = wp_remote_get( $full_src );

    if ( ! is_array( $response ) || is_wp_error(  $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $src;
    }
	
    if ( 'style_loader_src' === $current_filter ) {
        if ( ! class_exists( 'Minify_CSS_UriRewriter' ) ) {
            require( WP_ROCKET_PATH . 'min/lib/Minify/CSS/UriRewriter.php' );
        }
        // Rewrite import/url in CSS content to add the absolute path to the file
        $file_content = Minify_CSS_UriRewriter::rewrite( $response['body'], $full_src_path );
    } else {
        $file_content = $response['body'];
    }

    if ( ! is_dir( $cache_busting_paths['bustingpath'] ) ) {
        rocket_mkdir_p( $cache_busting_paths['bustingpath'] );
    }

    rocket_put_content( $cache_busting_paths['filepath'], $file_content );

    return $cache_busting_paths['url'];
}

/**
 * Create a static file for dynamically generated CSS/JS from PHP
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $src dynamic CSS/JS file URL.
 * @return string URL of the generated static file
 */
add_filter( 'style_loader_src', 'rocket_cache_dynamic_resource', 16 );
add_filter( 'script_loader_src', 'rocket_cache_dynamic_resource', 16 );
function rocket_cache_dynamic_resource( $src ) {
    global $pagenow;
	
	if ( 'wp-login.php' == $pagenow ) {
    	return $src;
    }

    if ( false === strpos( $src, '.php' ) ) {
        return $src;
    }

    $full_src = ( substr( $src, 0, 2 ) === '//' ) ? rocket_add_url_protocol( $src ) : $src;

	switch ( current_filter() ) {
		case 'script_loader_src':
			$extension = '.js';
			break;
		case 'style_loader_src':
			$extension = '.css';
			break;
	}

	$hosts 		 = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
    $hosts[] 	 = parse_url( home_url(), PHP_URL_HOST );
    $hosts_index = array_flip( $hosts );
    list( $file_host, $relative_src_path ) = get_rocket_parse_url( $full_src );

	if ( $file_host == '' ) {
        $full_src = home_url() . $src;
    }

    if ( false !== strpos( $full_src, '://' ) && ! isset( $hosts_index[ $file_host ] ) ) {
        return $src;
    }

    $relative_src_path = ltrim( $relative_src_path, '/' );
    /*
     * Filters the dynamic resource cache filename
     *
     * @since 2.9
     * @author Remy Perona
     *
     * @param string $filename filename for the cache file
     */
    $cache_dynamic_resource_filename = apply_filters( 'rocket_dynamic_resource_cache_filename', preg_replace( '/\.(php)$/', $extension, strtok( rtrim( str_replace( '/', '-', $relative_src_path ) ), '?' ) ) );
    $cache_busting_paths             = rocket_get_cache_busting_paths( $cache_dynamic_resource_filename, $extension );

    if ( file_exists( $cache_busting_paths['filepath'] ) && is_readable( $cache_busting_paths['filepath'] ) ) {
    	return $cache_busting_paths['url'];
    }

    $response = wp_remote_get( $full_src );

    if ( ! is_array( $response ) || is_wp_error(  $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $src;
    }

    if ( ! is_dir( $cache_busting_paths['bustingpath'] ) ) {
        rocket_mkdir_p( $cache_busting_paths['bustingpath'] );
    }

    rocket_put_content( $cache_busting_paths['filepath'], $response['body'] );

    return $cache_busting_paths['url'];
}