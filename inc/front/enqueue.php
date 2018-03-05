<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Wrapper for get_rocket_browser_cache_busting except when minification is active.
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $src CSS/JS file URL.
 * @return string updated CSS/JS file URL.
 */
function rocket_browser_cache_busting( $src ) {
	$current_filter = current_filter();

	if ( 'style_loader_src' === $current_filter && get_rocket_option( 'minify_css' ) && ( ! defined( 'DONOTMINIFYCSS' ) || ! DONOTMINIFYCSS ) && ! is_rocket_post_excluded_option( 'minify_css' ) ) {
		return $src;
	}

	if ( 'script_loader_src' === $current_filter && get_rocket_option( 'minify_js' ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
		return $src;
	}

	return get_rocket_browser_cache_busting( $src, $current_filter );
}
add_filter( 'style_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX );
add_filter( 'script_loader_src', 'rocket_browser_cache_busting', PHP_INT_MAX );

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
function get_rocket_browser_cache_busting( $src, $current_filter = '' ) {
	global $pagenow;

	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $src;
	}

	if ( ! get_rocket_option( 'remove_query_strings' ) ) {
		return $src;
	}

	if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user', 0 ) ) {
		return $src;
	}

	if ( 'wp-login.php' === $pagenow ) {
		return $src;
	}

	if ( false === strpos( $src, '.css' ) && false === strpos( $src, '.js' ) ) {
		return $src;
	}

	if ( false !== strpos( $src, 'ver=' . $GLOBALS['wp_version'] ) ) {
		$src = rtrim( str_replace( array( 'ver=' . $GLOBALS['wp_version'], '?&', '&&' ), array( '', '?', '&' ), $src ), '?&' );
	}

	/**
	 * Filters files to exclude from cache busting
	 *
	 * @since 2.9.3
	 * @author Remy Perona
	 *
	 * @param array $excluded_files An array of filepath to exclude.
	 */
	$excluded_files = apply_filters( 'rocket_exclude_cache_busting', array() );
	$excluded_files = implode( '|', $excluded_files );

	if ( preg_match( '#^(' . $excluded_files . ')$#', rocket_clean_exclude_file( $src ) ) ) {
		return $src;
	}

	if ( empty( $current_filter ) ) {
		$current_filter = current_filter();
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

	$hosts         = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
	$hosts['home'] = rocket_extract_url_component( home_url(), PHP_URL_HOST );
	$hosts_index   = array_flip( $hosts );
	$file          = get_rocket_parse_url( $full_src );

	if ( '' === $file['host'] ) {
		$full_src = home_url() . $src;
	}

	if ( false !== strpos( $full_src, '://' ) && ! isset( $hosts_index[ $file['host'] ] ) ) {
		return $src;
	}

	if ( empty( $file['query'] ) ) {
		return $src;
	}

	$relative_src           = ltrim( $file['path'] . '?' . $file['query'], '/' );
	$abspath_src            = rocket_url_to_path( strtok( $full_src, '?' ), $hosts_index );
	$cache_busting_filename = preg_replace( '/\.(js|css)\?(?:timestamp|ver)=([^&]+)(?:.*)/', '-$2.$1', $relative_src );

	if ( $cache_busting_filename === $relative_src ) {
		return $src;
	}

	/*
	 * Filters the cache busting filename
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param string $filename filename for the cache busting file
	 */
	$cache_busting_filename = apply_filters( 'rocket_cache_busting_filename', $cache_busting_filename );
	$cache_busting_paths    = rocket_get_cache_busting_paths( $cache_busting_filename, $extension );

	if ( file_exists( $cache_busting_paths['filepath'] ) && is_readable( $cache_busting_paths['filepath'] ) ) {
		return $cache_busting_paths['url'];
	}

	if ( rocket_fetch_and_cache_busting( $abspath_src, $cache_busting_paths, $abspath_src, $current_filter ) ) {
		return $cache_busting_paths['url'];
	}

	return $src;
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
function rocket_cache_dynamic_resource( $src ) {
	global $pagenow;

	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $src;
	}

	if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user' ) ) {
		return $src;
	}

	if ( 'wp-login.php' === $pagenow ) {
		return $src;
	}

	if ( false === strpos( $src, '.php' ) ) {
		return $src;
	}

	/**
	 * Filters files to exclude from static dynamic resources
	 *
	 * @since 2.9.3
	 * @author Remy Perona
	 *
	 * @param array $excluded_files An array of filepath to exclude.
	 */
	$excluded_files   = apply_filters( 'rocket_exclude_static_dynamic_resources', array() );
	$excluded_files[] = '/wp-admin/admin-ajax.php';
	$excluded_files   = array_flip( $excluded_files );

	if ( isset( $excluded_files[ rocket_clean_exclude_file( $src ) ] ) ) {
		return $src;
	}

	$full_src = ( substr( $src, 0, 2 ) === '//' ) ? rocket_add_url_protocol( $src ) : $src;

	$current_filter = current_filter();

	switch ( $current_filter ) {
		case 'script_loader_src':
			$extension  = 'js';
			$minify_key = get_rocket_option( 'minify_js_key' );
			break;
		case 'style_loader_src':
			$extension  = 'css';
			$minify_key = get_rocket_option( 'minify_css_key' );
			break;
	}

	$hosts         = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
	$hosts[]       = rocket_extract_url_component( home_url(), PHP_URL_HOST );
	$hosts_index   = array_flip( $hosts );
	$file          = get_rocket_parse_url( $full_src );
	$file['query'] = remove_query_arg( 'ver', $file['query'] );

	if ( $file['query'] ) {
		return $src;
	}

	if ( '' === $file['host'] ) {
		$full_src = home_url() . $src;
	}

	if ( strpos( $full_src, '://' ) !== false && ! isset( $hosts_index[ $file['host'] ] ) ) {
		return $src;
	}

	$relative_src = ltrim( $file['path'], '/' );
	$abspath_src  = rocket_url_to_path( strtok( $full_src, '?' ), $hosts_index );

	/*
	 * Filters the dynamic resource cache filename
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param string $filename filename for the cache file
	 */
	$cache_dynamic_resource_filename = apply_filters( 'rocket_dynamic_resource_cache_filename', preg_replace( '/\.(php)$/', '-' . $minify_key . '.' . $extension, $relative_src ) );
	$cache_busting_paths             = rocket_get_cache_busting_paths( $cache_dynamic_resource_filename, $extension );

	if ( file_exists( $cache_busting_paths['filepath'] ) && is_readable( $cache_busting_paths['filepath'] ) ) {
		return $cache_busting_paths['url'];
	}

	if ( rocket_fetch_and_cache_busting( $full_src, $cache_busting_paths, $abspath_src, $current_filter ) ) {
		return $cache_busting_paths['url'];
	}

	return $src;
}
add_filter( 'style_loader_src', 'rocket_cache_dynamic_resource', 16 );
add_filter( 'script_loader_src', 'rocket_cache_dynamic_resource', 16 );
