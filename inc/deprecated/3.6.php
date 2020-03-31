
<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

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

/**
 * Delete all cache busting files.
 *
 * @since  2.9
 * @since  3.6 Deprecated
 * @author Remy Perona
 * @deprecated
 *
 * @param  string|array $extensions (default: array('js','css') File extensions to clean.
 * @return void
 */
function rocket_clean_cache_busting( $extensions = [ 'js', 'css' ] ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6' );

	$extensions = is_string( $extensions ) ? (array) $extensions : $extensions;

	$cache_busting_path = WP_ROCKET_CACHE_BUSTING_PATH . get_current_blog_id();

	if ( ! rocket_direct_filesystem()->is_dir( $cache_busting_path ) ) {
		rocket_mkdir_p( $cache_busting_path );

		Logger::debug(
			'No Cache Busting folder found.',
			[
				'mkdir cache busting folder',
				'cache_busting_path' => $cache_busting_path,
			]
		);

		return;
	}

	try {
		$dir = new RecursiveDirectoryIterator( $cache_busting_path, FilesystemIterator::SKIP_DOTS );
	} catch ( \UnexpectedValueException $e ) {
		// No logging yet.
		return;
	}

	try {
		$iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST );
	} catch ( \Exception $e ) {
		// No logging yet.
		return;
	}

	foreach ( $extensions as $ext ) {
		/**
		 * Fires before the cache busting files are deleted
		 *
		 * @since 2.9
		 *
		 * @param string $ext File extensions to clean.
		*/
		do_action( 'before_rocket_clean_busting', $ext ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		try {
			$files = new RegexIterator( $iterator, '#.*\.' . $ext . '#', RegexIterator::GET_MATCH );
			foreach ( $files as $file ) {
				rocket_direct_filesystem()->delete( $file[0] );
			}
		} catch ( \InvalidArgumentException $e ) {
			// No logging yet.
			return;
		}

		/**
		 * Fires after the cache busting files was deleted
		 *
		 * @since 2.9
		 *
		 * @param string $ext File extensions to clean.
		*/
		do_action( 'after_rocket_clean_cache_busting', $ext ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	try {
		foreach ( $iterator as $item ) {
			if ( rocket_direct_filesystem()->is_dir( $item ) ) {
				rocket_direct_filesystem()->delete( $item );
			}
		}
	} catch ( \UnexpectedValueException $e ) {
		// Log the error.
		Logger::debug(
			'Cache Busting folder structure contains a directory we cannot recurse into.',
			[
				'Full error',
				'UnexpectedValueException' => $e->getMessage(),
			]
		);
	}
}

/**
 * Returns paths used for cache busting.
 *
 * @since  2.9
 * @since  3.6 Deprecated
 * @author Remy Perona
 * @deprecated
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
