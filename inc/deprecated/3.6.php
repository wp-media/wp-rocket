<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

class_alias( '\\WP_Rocket\\Engine\\Admin\\Beacon\\ServiceProvider', '\\WP_Rocket\\ServiceProvider\\Beacon' );

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

/**
 * Require deprecated classes.
 */
require_once __DIR__ . '/DeprecatedClassTrait.php';
require_once __DIR__ . '/Engine/Optimization/QueryString/Remove.php';
require_once __DIR__ . '/Engine/Optimization/QueryString/RemoveSubscriber.php';

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\Engine\Optimization\ServiceProvider', '\WP_Rocket\ServiceProvider\Optimization_Subscribers' );

/**
 * This warning is displayed when the busting cache dir isn't writeable
 *
 * @since 2.9
 * @deprecated 3.6
 * @author Remy Perona
 */
function rocket_warning_busting_cache_dir_permissions() {
	_deprecated_function( __FUNCTION__ . '()', '3.6' );
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_BUSTING_PATH ) )
		&& ( get_rocket_option( 'remove_query_strings', false ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_BUSTING_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
