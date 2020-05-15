<?php

defined( 'ABSPATH' ) || exit;

/**
 * Require deprecated classes.
 */
require_once __DIR__ . '/DeprecatedClassTrait.php';
require_once __DIR__ . '/Engine/Optimization/QueryString/Remove.php';
require_once __DIR__ . '/Engine/Optimization/QueryString/RemoveSubscriber.php';

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\Engine\Admin\Beacon\ServiceProvider', '\WP_Rocket\ServiceProvider\Beacon' );
class_alias( '\WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck', '\WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber' );
class_alias( '\WP_Rocket\Engine\HealthCheck\HealthCheck', '\WP_Rocket\Engine\Admin\HealthCheck' );
class_alias( '\WP_Rocket\Engine\Optimization\ServiceProvider', '\WP_Rocket\ServiceProvider\Optimization_Subscribers' );
class_alias( '\WP_Rocket\ThirdParty\Plugins\Smush', '\WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber' );

/**
 * Exclude fusion styles from cache busting to prevent cache dir issues
 *
 * @deprecated 3.6
 * @author Remy Perona
 *
 * @param array $excluded_files An array of excluded files.
 * @return array
 */
function rocket_exclude_avada_dynamic_css( $excluded_files ) {
    _deprecated_function( __FUNCTION__ . '()', '3.6' );

    $upload_dir = wp_upload_dir();

    $excluded_files[] = rocket_clean_exclude_file( $upload_dir['baseurl'] . '/fusion-styles/(.*)' );

    return $excluded_files;
}

/**
 * Excludes Uncode JS files from remove query strings
 *
 * @deprecated 3.6
 * @since 3.3.3
 * @author Remy Perona
 *
 * @param array $exclude_busting Array of CSS and JS filepaths to be excluded.
 * @return array
 */
function rocket_exclude_busting_uncode( $exclude_busting ) {
    _deprecated_function( __FUNCTION__ . '()', '3.6' );

    // CSS files.
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/style.css' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/uncode-icons.css' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/style-custom.css' );

    // JS files.
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/init.min.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/app.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/app.min.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/plugins.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/plugins.min.js' );
    return $exclude_busting;
}

/**
 * Purge the cache when the beaver builder layout is updated to update the minified files content & URL
 *
 * @deprecated 3.6
 * @since 2.9 Also clear the cache busting folder
 * @since 2.8.6
 */
function rocket_beaver_builder_clean_domain() {
	_deprecated_function( __FUNCTION__ . '()', '3.6', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache' );
	rocket_clean_minify();
	rocket_clean_domain();
}

/**
 * Returns paths used for cache busting
 *
 * @since 2.9
 * @deprecated 3.6
 * @author Remy Perona
 *
 * @param string $filename name of the cache busting file.
 * @param string $extension file extension.
 * @return array Array of paths used for cache busting
 */
function rocket_get_cache_busting_paths( $filename, $extension ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6' );
	$blog_id                = get_current_blog_id();
	$cache_busting_path     = WP_ROCKET_CACHE_BUSTING_PATH . $blog_id;
	$filename               = rocket_realpath( rtrim( str_replace( [ ' ', '%20' ], '-', $filename ) ) );
	$cache_busting_filepath = $cache_busting_path . $filename;
	$cache_busting_url      = WP_ROCKET_CACHE_BUSTING_URL . $blog_id . $filename;

	switch ( $extension ) {
		case 'css':
			/** This filter is documented in inc/functions/minify.php */
			$cache_busting_url = apply_filters( 'rocket_css_url', $cache_busting_url );
			break;
		case 'js':
			/** This filter is documented in inc/functions/minify.php */
			$cache_busting_url = apply_filters( 'rocket_js_url', $cache_busting_url );
			break;
	}

	return [
		'bustingpath' => $cache_busting_path,
		'filepath'    => $cache_busting_filepath,
		'url'         => $cache_busting_url,
	];
}

/**
 * Caches SCCSS code & remove the default enqueued URL
 *
 * @since 2.9
 * @deprecated 3.6
 *
 * @author Remy Perona
 */
function rocket_cache_sccss() {
	_deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::cache_sccss()' );
	$sccss = rocket_get_cache_busting_paths( 'sccss.css', 'css' );

	if ( ! file_exists( $sccss['filepath'] ) ) {
		rocket_sccss_create_cache_file( $sccss['bustingpath'], $sccss['filepath'] );
	}

	if ( file_exists( $sccss['filepath'] ) ) {
		wp_enqueue_style( 'scss', $sccss['url'], '', filemtime( $sccss['filepath'] ) );
		remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
	}
}

/**
 * Deletes & recreates cache for SCCSS code
 *
 * @since 2.9
 * @deprecated 3.6
 *
 * @author Remy Perona
 */
function rocket_delete_sccss_cache_file() {
	_deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::update_cache_file()' );
	$sccss = rocket_get_cache_busting_paths( 'sccss.css', 'css' );

	array_map( 'unlink', glob( $sccss['bustingpath'] . 'sccss*.css' ) );
	rocket_clean_domain();
	rocket_sccss_create_cache_file( $sccss['bustingpath'], $sccss['filepath'] );
}

/**
 * Returns the filename for SCSSS cache file
 *
 * @since 2.9
 * @deprecated 3.6
 *
 * @author Remy Perona
 *
 * @param string $filename filename.
 * @return string filename
 */
function rocket_sccss_cache_busting_filename( $filename ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6' );
	if ( false !== strpos( $filename, 'sccss' ) ) {
		return preg_replace( '/(?:.*)(sccss(?:.*))/i', '$1', $filename );
	}

	return $filename;
}

/**
 * Creates the cache file for SCCSS code
 *
 * @since 2.9
 * @deprecated 3.6
 *
 * @author Remy Perona
 *
 * @param string $cache_busting_path Path to the cache busting directory.
 * @param string $cache_sccss_filepath Path to the sccss cache file.
 */
function rocket_sccss_create_cache_file( $cache_busting_path, $cache_sccss_filepath ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::create_cache_file()' );
	$options     = get_option( 'sccss_settings' );
	$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
	$content     = wp_kses( $raw_content, [ '\'', '\"' ] );
	$content     = str_replace( '&gt;', '>', $content );

	if ( ! rocket_direct_filesystem()->is_dir( $cache_busting_path ) ) {
		rocket_mkdir_p( $cache_busting_path );
	}

	rocket_put_content( $cache_sccss_filepath, $content );
}

