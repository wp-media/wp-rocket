<?php

use WP_Rocket\Logger\Logger;
use WP_Rocket\Engine\Cache\AdvancedCache;

defined( 'ABSPATH' ) || exit;

/**
 * Creates the advanced-cache.php file.
 *
 * @since 3.6 Uses AdvancedCache::get_advanced_cache_content().
 * @since 2.0
 *
 * @param AdvancedCache $advanced_cache Optional. Instance of the advanced cache handler.
 */
function rocket_generate_advanced_cache_file( $advanced_cache = null ) {
	/**
	 * Filters whether to generate the advanced-cache.php file.
	 *
	 * @since 3.6.3
	 *
	 * @param bool True (default) to go ahead with advanced cache file generation; false to stop generation.
	 */
	if ( ! (bool) apply_filters( 'rocket_generate_advanced_cache_file', true ) ) {
		return false;
	}

	static $done = false;

	if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
		$done = false;
	}

	if ( $done ) {
		return false;
	}
	$done = true;

	if ( is_null( $advanced_cache ) ) {
		$container      = apply_filters( 'rocket_container', null );
		$advanced_cache = $container->get( 'advanced_cache' );
	}

	return rocket_put_content(
		rocket_get_constant( 'WP_CONTENT_DIR' ) . '/advanced-cache.php',
		$advanced_cache->get_advanced_cache_content()
	);
}

/**
 * Generates the configuration file for the current domain based on the values of options
 *
 * @since 2.0
 *
 * @return array Names of all config files & The content that will be printed
 */
function get_rocket_config_file() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$options = get_option( WP_ROCKET_SLUG );

	if ( ! $options ) {
		return [
			[],
			'',
		];
	}

	$buffer  = "<?php\n";
	$buffer .= "defined( 'ABSPATH' ) || exit;\n\n";

	$buffer .= '$rocket_cookie_hash = \'' . COOKIEHASH . "';\n";
	$buffer .= '$rocket_logged_in_cookie = \'' . LOGGED_IN_COOKIE . "';\n";

	/**
	 * Filters the activation of the common cache for logged-in users.
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param bool True to activate the common cache, false to ignore.
	 */
	if ( apply_filters( 'rocket_common_cache_logged_users', false ) ) {
		$buffer .= '$rocket_common_cache_logged_users = 1;' . "\n";
	}

	if ( ! empty( $options['cache_webp'] ) ) {
		/** This filter is documented in inc/classes/buffer/class-cache.php */
		$disable_webp_cache = apply_filters( 'rocket_disable_webp_cache', false );

		if ( $disable_webp_cache ) {
			$options['cache_webp'] = 0;
		}
	}

	/**
	 * Filters the use of the mobile cache version for tablets
	 * 'desktop' will serve desktop to tablets, 'mobile' will serve mobile to tablets
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param string $tablet_version valid values are 'mobile' or 'desktop'
	 */
	$buffer .= '$rocket_cache_mobile_files_tablet = \'' . apply_filters( 'rocket_cache_mobile_files_tablet', 'desktop' ) . "';\n";

	foreach ( $options as $option => $value ) {
		if ( 'cache_ssl' === $option ) {
			if ( 1 !== (int) $value ) {
				if ( rocket_is_ssl_website() ) {
					update_rocket_option( 'cache_ssl', 1 );
					$value = 1;
				}
			}

			$buffer .= '$rocket_' . $option . ' = ' . (int) $value . ";\n";
		}

		if ( 'cache_mobile' === $option || 'do_caching_mobile_files' === $option || 'cache_webp' === $option ) {
			$buffer .= '$rocket_' . $option . ' = ' . (int) $value . ";\n";
		}

		if ( 'secret_cache_key' === $option ) {
			$buffer .= '$rocket_' . $option . ' = \'' . $value . "';\n";
		}

		if ( 'cache_reject_uri' === $option ) {
			$buffer .= '$rocket_' . $option . ' = \'' . get_rocket_cache_reject_uri() . "';\n";
		}

		if ( 'cache_query_strings' === $option ) {
			$buffer .= '$rocket_' . $option . ' = ' . call_user_func( 'var_export', get_rocket_cache_query_string(), true ) . ";\n";
		}

		if ( 'cache_reject_cookies' === $option ) {
			$cookies = get_rocket_cache_reject_cookies();

			if ( $cookies && get_rocket_option( 'cache_logged_user' ) ) {
				// Make sure the "logged-in cookies" are not rejected.
				$logged_in_cookie = explode( COOKIEHASH, LOGGED_IN_COOKIE );
				$logged_in_cookie = array_map( 'preg_quote', $logged_in_cookie );
				$logged_in_cookie = implode( '[^|]*', $logged_in_cookie );
				$cookies          = preg_replace( '/\|' . $logged_in_cookie . '\|/', '|', '|' . $cookies . '|' );
				$cookies          = trim( $cookies, '|' );
			}

			$buffer .= '$rocket_' . $option . ' = \'' . $cookies . "';\n";
		}

		if ( 'cache_reject_ua' === $option ) {
			$buffer .= '$rocket_' . $option . ' = \'' . get_rocket_cache_reject_ua() . "';\n";
		}
	}

	$buffer .= '$rocket_cache_ignored_parameters = ' . call_user_func( 'var_export', rocket_get_ignored_parameters(), true ) . ";\n";
	$buffer .= '$rocket_cache_mandatory_cookies = ' . call_user_func( 'var_export', get_rocket_cache_mandatory_cookies(), true ) . ";\n";

	$buffer .= '$rocket_cache_dynamic_cookies = ' . call_user_func( 'var_export', get_rocket_cache_dynamic_cookies(), true ) . ";\n";

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', false ) ) {
		$buffer .= '$rocket_url_no_dots = 1;';
	}

	$config_files_path = [];
	$urls              = [ rocket_get_home_url() ];

	// Check if a translation plugin is activated and this configuration is in subdomain.
	$subdomains = get_rocket_i18n_subdomains();

	if ( $subdomains ) {
		$urls = $subdomains;
	}

	foreach ( $urls as $url ) {
		$file                = get_rocket_parse_url( untrailingslashit( $url ) );
		$file['path']        = ( ! empty( $file['path'] ) ) ? str_replace( '/', '.', untrailingslashit( $file['path'] ) ) : '';
		$config_files_path[] = WP_ROCKET_CONFIG_PATH . strtolower( $file['host'] ) . $file['path'] . '.php';
	}

	/**
	 * Filter all config files path
	 *
	 * @since 2.6.5
	 *
	 * @param array $config_files_path  Path of all config files.
	*/
	$config_files_path = apply_filters( 'rocket_config_files_path', $config_files_path );

	/**
	 * Filter the content of all config files
	 *
	 * @since 2.1
	 *
	 * @param string $buffer The content that will be printed.
	 * @param array $config_files_path  Names of all config files.
	*/
	$buffer = apply_filters( 'rocket_config_file', $buffer, $config_files_path );
	$buffer = preg_replace( '@array\s+\(@i', 'array(', $buffer );
	$buffer = preg_replace( '@array\(\s+\)@i', 'array()', $buffer );

	return [ $config_files_path, $buffer ];
}

/**
 * Create the current config domain file
 * For example, if home_url() return example.com, the config domain file will be in /config/example.com
 *
 * @since 2.0
 *
 * @return void
 */
function rocket_generate_config_file() {
	list( $config_files_path, $buffer ) = get_rocket_config_file();

	if ( count( $config_files_path ) ) {
		rocket_init_config_dir();

		foreach ( $config_files_path as $file ) {
			rocket_put_content( $file, $buffer );
		}
	}
}

/**
 * Remove the current config domain file
 *
 * @since 2.6
 *
 * @return void
 */
function rocket_delete_config_file() {
	list( $config_files_path ) = get_rocket_config_file();
	foreach ( $config_files_path as $config_file ) {
		rocket_direct_filesystem()->delete( $config_file );
	}
}

/**
 * Create all cache folders (wp-rocket & min)
 *
 * @since 2.6
 *
 * @return void
 */
function rocket_init_cache_dir() {
	global $is_apache;

	$filesystem = rocket_direct_filesystem();

	// Create cache folder if not exist.
	if ( ! $filesystem->is_dir( WP_ROCKET_CACHE_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CACHE_PATH );
	}

	if ( ! $filesystem->is_file( WP_ROCKET_CACHE_PATH . 'index.html' ) ) {
		$filesystem->touch( WP_ROCKET_CACHE_PATH . 'index.html' );
	}

	if ( $is_apache ) {
		$htaccess_path = WP_ROCKET_CACHE_PATH . '.htaccess';

		if ( ! $filesystem->is_file( $htaccess_path ) ) {
			$filesystem->touch( $htaccess_path );
			rocket_put_content( $htaccess_path, "<IfModule mod_autoindex.c>\nOptions -Indexes\n</IfModule>" );
		}
	}

	// Create minify cache folder if not exist.
	if ( ! $filesystem->is_dir( WP_ROCKET_MINIFY_CACHE_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_MINIFY_CACHE_PATH );
	}

	if ( ! $filesystem->is_file( WP_ROCKET_MINIFY_CACHE_PATH . 'index.html' ) ) {
		$filesystem->touch( WP_ROCKET_MINIFY_CACHE_PATH . 'index.html' );
	}

	// Create busting cache folder if not exist.
	if ( ! $filesystem->is_dir( WP_ROCKET_CACHE_BUSTING_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CACHE_BUSTING_PATH );
	}

	if ( ! $filesystem->is_file( WP_ROCKET_CACHE_BUSTING_PATH . 'index.html' ) ) {
		$filesystem->touch( WP_ROCKET_CACHE_BUSTING_PATH . 'index.html' );
	}

	// Create critical CSS folder if not exist.
	if ( ! $filesystem->is_dir( WP_ROCKET_CRITICAL_CSS_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CRITICAL_CSS_PATH );
	}

	if ( ! $filesystem->is_file( WP_ROCKET_CRITICAL_CSS_PATH . 'index.html' ) ) {
		$filesystem->touch( WP_ROCKET_CRITICAL_CSS_PATH . 'index.html' );
	}
}

/**
 * Create the config folder (wp-rocket-config)
 *
 * @since 2.6
 *
 * @return void
 */
function rocket_init_config_dir() {
	$filesystem = rocket_direct_filesystem();

	// Create config domain folder if not exist.
	if ( ! $filesystem->is_dir( WP_ROCKET_CONFIG_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CONFIG_PATH );
	}

	// Initialize the config directory with index.html to prevent indexing.
	if ( ! $filesystem->is_file( WP_ROCKET_CONFIG_PATH . 'index.html' ) ) {
		$filesystem->touch( WP_ROCKET_CONFIG_PATH . 'index.html' );
	}
}

/**
 * Delete all minify cache files.
 *
 * @since 3.5.3 Replaces glob.
 * @since 2.1
 *
 * @param string|array $extensions Optional. File extensions to minify. Default: js and css.
 */
function rocket_clean_minify( $extensions = [ 'js', 'css' ] ) {
	// Bails out if there are no extensions to target.
	if ( empty( $extensions ) ) {
		return;
	}

	if ( is_string( $extensions ) ) {
		$extensions = (array) $extensions;
	}

	$min_cache_path = rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' );
	$min_path       = $min_cache_path . get_current_blog_id() . '/';
	$iterator       = _rocket_get_cache_path_iterator( $min_path );
	if ( false === $iterator ) {
		return;
	}

	$filesystem     = rocket_direct_filesystem();
	$min_path_regex = str_replace( '/', '\/', $min_path );

	foreach ( $extensions as $ext ) {
		/**
		 * Fires before the minify cache files are deleted.
		 *
		 * @since 2.1
		 *
		 * @param string $ext File extensions to minify.
		 */
		do_action( 'before_rocket_clean_minify', $ext ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		try {
			$entries = new RegexIterator( $iterator, "/{$min_path_regex}.*\.{$ext}/" );
		} catch ( Exception $e ) {
			return;
		}

		foreach ( $entries as $entry ) {
			$filesystem->delete( $entry->getPathname() );
		}

		/**
		 * Fires after the minify cache files was deleted.
		 *
		 * @since 2.1
		 *
		 * @param string $ext File extensions to minify.
		 */
		do_action( 'after_rocket_clean_minify', $ext ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	// Delete any directories.
	foreach ( $iterator as $item ) {
		if ( $filesystem->is_dir( $item ) ) {
			$filesystem->delete( $item );
		}
	}

	// Clean the cache/min/3rd-party items.
	try {
		$files = new FilesystemIterator( "{$min_cache_path}3rd-party" );

		foreach ( $files as $file ) {
			if ( $filesystem->is_file( $file ) ) {
				$filesystem->delete( $file );
			}
		}
	} catch ( UnexpectedValueException $e ) {
		// No logging yet.
		return;
	}
}

/**
 * Delete all cache busting files.
 *
 * @since 2.9
 *
 * @param  string|array $extensions (default: array('js','css') File extensions to clean.
 * @return void
 */
function rocket_clean_cache_busting( $extensions = [ 'js', 'css' ] ) {
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
	} catch ( UnexpectedValueException $e ) {
		// No logging yet.
		return;
	}

	try {
		$iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST );
	} catch ( Exception $e ) {
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
		} catch ( InvalidArgumentException $e ) {
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
	} catch ( UnexpectedValueException $e ) {
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
 * Delete one or several cache files.
 *
 * @since 3.5.5 Optimizes by grabbing root cache dirs once, bailing out when file/dir doesn't exist, & directly
 *        deleting files.
 * @since 3.5.4 Replaces glob and optimizes.
 * @since 2.0   Delete cache files for all users.
 * @since 1.1.0 Add filter rocket_clean_files.
 * @since 1.0
 *
 * @param string|array              $urls       URLs of cache files to be deleted.
 * @param WP_Filesystem_Direct|null $filesystem Optional. Instance of filesystem handler.
 */
function rocket_clean_files( $urls, $filesystem = null ) {
	$urls = (array) $urls;
	if ( empty( $urls ) ) {
		return;
	}

	$urls = array_filter( $urls );
	if ( empty( $urls ) ) {
		return;
	}

	/** This filter is documented in inc/front/htaccess.php */
	$url_no_dots = (bool) apply_filters( 'rocket_url_no_dots', false );
	$cache_path  = _rocket_get_wp_rocket_cache_path();

	if ( empty( $filesystem ) ) {
		$filesystem = rocket_direct_filesystem();
	}

	/**
	 * Fires before all cache files are deleted.
	 *
	 * @since  3.2.2
	 *
	 * @param array $urls The URLs corresponding to the deleted cache files.
	 */
	do_action( 'before_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	foreach ( $urls as $url ) {

		/**
		 * Fires before the cache file is deleted.
		 *
		 * @since 1.0
		 *
		 * @param string $url The URL that the cache file to be deleted.
		 */
		do_action( 'before_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( $url_no_dots ) {
			$url = str_replace( '.', '_', $url );
		}

		$parsed_url = get_rocket_parse_url( $url );

		if ( ! empty( $parsed_url['host'] ) ) {
			foreach ( _rocket_get_cache_dirs( $parsed_url['host'], $cache_path ) as $dir ) {
				$entry = $dir . $parsed_url['path'];
				// Skip if the dir/file does not exist.
				if ( ! $filesystem->exists( $entry ) ) {
					continue;
				}

				if ( $filesystem->is_dir( $entry ) ) {
					rocket_rrmdir( $entry, [], $filesystem );
				} else {
					$filesystem->delete( $entry );
				}
			}
		}

		/**
		 * Fires after the cache file is deleted.
		 *
		 * @since 1.0
		 *
		 * @param string $url The URL that the cache file was deleted.
		 */
		do_action( 'after_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Fires after all cache files are deleted.
	 *
	 * @since  3.2.2
	 *
	 * @param array $urls The URLs corresponding to the deleted cache files.
	 */
	do_action( 'after_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Remove the home cache file and pagination
 *
 * $since 2.2 Add $lang argument
 *
 * @since 2.0 Delete cache files for all users
 * @since 1.0
 *
 * @param string $lang (default: '') The language code.
 * @return void
 */
function rocket_clean_home( $lang = '' ) {
	$parse_url = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', false ) ) {
		$parse_url['host'] = str_replace( '.', '_', $parse_url['host'] );
	}

	$root = WP_ROCKET_CACHE_PATH . $parse_url['host'] . '*' . untrailingslashit( $parse_url['path'] );

	/**
	 * Filter the homepage caching folder root
	 *
	 * @since 2.6.5
	 * @param array     $root The root that will be returned.
	 * @param string    $host The website host.
	 * @param string    $path The website path.
	*/
	$root = apply_filters( 'rocket_clean_home_root', $root, $parse_url['host'], $parse_url['path'] );

	/**
	 * Fires before the home cache file is deleted
	 *
	 * @since 1.0
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	*/
	do_action( 'before_rocket_clean_home', $root, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	// Delete homepage.
	$files = glob( $root . '/{index,index-*}.{html,html_gzip}', GLOB_BRACE | GLOB_NOSORT );
	if ( $files ) {
		foreach ( $files as $file ) { // no array map to use @.
			rocket_direct_filesystem()->delete( $file );
		}
	}

	// Delete homepage pagination.
	$dirs = glob( $root . '*/' . $GLOBALS['wp_rewrite']->pagination_base, GLOB_NOSORT );
	if ( $dirs ) {
		foreach ( $dirs as $dir ) {
			rocket_rrmdir( $dir );
		}
	}

	// Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
	$nginx_mobile_detect_files = glob( $root . '/.mobile-active', GLOB_BRACE | GLOB_NOSORT );
	if ( $nginx_mobile_detect_files ) {
		foreach ( $nginx_mobile_detect_files as $nginx_mobile_detect_file ) { // no array map to use @.
			rocket_direct_filesystem()->delete( $nginx_mobile_detect_file );
		}
	}

	// Remove the hidden empty file for webp.
	$nowebp_detect_files = glob( $root . '/.no-webp', GLOB_BRACE | GLOB_NOSORT );
	if ( $nowebp_detect_files ) {
		foreach ( $nowebp_detect_files as $nowebp_detect_file ) { // no array map to use @.
			rocket_direct_filesystem()->delete( $nowebp_detect_file );
		}
	}

	/**
	 * Fires after the home cache file was deleted
	 *
	 * @since 1.0
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	*/
	do_action( 'after_rocket_clean_home', $root, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Remove the home cache feed
 *
 * @since 2.7
 *
 * @return void
 */
function rocket_clean_home_feeds() {

	$urls   = [];
	$urls[] = get_feed_link();
	$urls[] = get_feed_link( 'comments_' );

	/**
	 * Filter the home feeds urls
	 *
	 * @since 2.7
	 * @param array     $urls The urls of the home feeds.
	*/
	$urls = apply_filters( 'rocket_clean_home_feeds', $urls );

	/**
	 * Fires before the home feeds cache is deleted
	 *
	 * @since 2.7
	 *
	 * @param array $urls The urls of the home feeds.
	*/
	do_action( 'before_rocket_clean_home_feeds', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	rocket_clean_files( $urls );

	/**
	 * Fires after the home feeds cache was deleted
	 *
	 * @since 2.7
	 *
	 * @param array $urls The urls of the home feeds.
	*/
	do_action( 'after_rocket_clean_home_feeds', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Remove all cache files for the domain.
 *
 * @since 3.5.5 Optimizes by grabbing root cache dirs once, bailing out when file/dir doesn't exist, & directly
 *        deleting files.
 * @since 3.5.3 Replaces glob with SPL.
 * @since 2.0   Delete domain cache files for all users
 * @since 1.0
 *
 * @param string                    $lang       Optional. The language code. Default: empty string.
 * @param WP_Filesystem_Direct|null $filesystem Optional. Instance of filesystem handler.
 */
function rocket_clean_domain( $lang = '', $filesystem = null ) {
	$urls = ( ! $lang || is_object( $lang ) || is_array( $lang ) || is_int( $lang ) )
		? (array) get_rocket_i18n_uri()
		: (array) get_rocket_i18n_home_url( $lang );

	/**
	 * Filter URLs to delete all caching files from a domain.
	 *
	 * @since 2.6.4
	 *
	 * @param array     URLs that will be returned.
	 * @param string    The language code.
	 */
	$urls = (array) apply_filters( 'rocket_clean_domain_urls', $urls, $lang );
	$urls = array_filter( $urls );
	if ( empty( $urls ) ) {
		return false;
	}

	/** This filter is documented in inc/front/htaccess.php */
	$url_no_dots      = (bool) apply_filters( 'rocket_url_no_dots', false );
	$cache_path       = _rocket_get_wp_rocket_cache_path();
	$dirs_to_preserve = get_rocket_i18n_to_preserve( $lang, $cache_path );

	if ( empty( $filesystem ) ) {
		$filesystem = rocket_direct_filesystem();
	}

	foreach ( $urls as $url ) {
		$parsed_url = get_rocket_parse_url( $url );

		if ( $url_no_dots ) {
			$parsed_url['host'] = str_replace( '.', '_', $parsed_url['host'] );
		}

		$root = $cache_path . $parsed_url['host'] . $parsed_url['path'];

		/**
		 * Fires before all cache files are deleted.
		 *
		 * @since 1.0
		 *
		 * @param string $root The path of home cache file.
		 * @param string $lang The current lang to purge.
		 * @param string $url  The home url.
		 */
		do_action( 'before_rocket_clean_domain', $root, $lang, $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		foreach ( _rocket_get_cache_dirs( $parsed_url['host'], $cache_path ) as $dir ) {
			$entry = $dir . $parsed_url['path'];
			// Skip if the dir/file does not exist.
			if ( ! $filesystem->exists( $entry ) ) {
				continue;
			}

			if ( $filesystem->is_dir( $entry ) ) {
				rocket_rrmdir( $entry, $dirs_to_preserve, $filesystem );
			} else {
				$filesystem->delete( $entry );
			}
		}

		/**
		 * Fires after all cache files was deleted.
		 *
		 * @since 1.0
		 *
		 * @param string $root The path of home cache file.
		 * @param string $lang The current lang to purge.
		 * @param string $url  The home url.
		 */
		do_action( 'after_rocket_clean_domain', $root, $lang, $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	return true;
}

/**
 * Delete the caching files of a specific term.
 *
 * $since 2.6.8
 *
 * @param  int    $term_id       The term ID.
 * @param  string $taxonomy_slug The taxonomy slug.
 * @return void
 */
function rocket_clean_term( $term_id, $taxonomy_slug ) {
	$purge_urls = [];

	// Get all term infos.
	$term = get_term_by( 'id', $term_id, $taxonomy_slug );

	// Get the term language.
	$i18n_plugin = rocket_has_i18n();

	if ( 'wpml' === $i18n_plugin && ! rocket_is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
		// WPML.
		$lang = $GLOBALS['sitepress']->get_language_for_element( $term_id, 'tax_' . $taxonomy_slug );
	} elseif ( 'polylang' === $i18n_plugin ) {
		// Polylang.
		$lang = pll_get_term_language( $term_id );
	} else {
		$lang = false;
	}

	// Get permalink.
	$permalink = get_term_link( $term, $taxonomy_slug );

	// Add permalink.
	if ( '/' !== rocket_extract_url_component( $permalink, PHP_URL_PATH ) ) {
		array_push( $purge_urls, $permalink );
	}

	/**
	 * Fires before deleted caching files related with the term
	 *
	 * @since 2.6.8
	 * @param obj    $term       The term object.
	 * @param array  $purge_urls URLs cache files to remove.
	 * @param string $lang       The term language.
	*/
	do_action( 'before_rocket_clean_term', $term, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	/**
	 * Filter URLs cache files to remove
	 *
	 * @since 2.6.8
	 * @param array $purge_urls List of URLs cache files to remove.
	 * @param obj   $term       The term object.
	*/
	$purge_urls = apply_filters( 'rocket_term_purge_urls', $purge_urls, $term );

	// Purge all files.
	rocket_clean_files( $purge_urls );

	// Never forget to purge homepage and their pagination.
	rocket_clean_home( $lang );

	/**
	 * Fires before deleted caching files related with the term
	 *
	 * @since 2.6.8
	 * @param obj    $term       The term object.
	 * @param array  $purge_urls URLs cache files to remove.
	 * @param string $lang       The term language.
	*/
	do_action( 'after_rocket_clean_term', $term, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Delete the caching files of a specific user
 *
 * $since 2.6.12
 *
 * @param int    $user_id  The user ID.
 * @param string $lang     The language code.
 * @return void
 */
function rocket_clean_user( $user_id, $lang = '' ) {
	$urls = ( ! $lang || is_object( $lang ) ) ? get_rocket_i18n_uri() : get_rocket_i18n_home_url( $lang );
	$urls = (array) $urls;

	/** This filter is documented in inc/functions/files.php */
	$urls = apply_filters( 'rocket_clean_domain_urls', $urls, $lang );
	$urls = array_filter( $urls );
	$user = get_user_by( 'id', $user_id );

	if ( ! $user ) {
		return;
	}

	$user_key = $user->user_login . '-' . get_rocket_option( 'secret_cache_key' );

	foreach ( $urls as $url ) {
		$parse_url = get_rocket_parse_url( $url );

		/** This filter is documented in inc/front/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$parse_url['host'] = str_replace( '.', '_', $parse_url['host'] );
		}

		$root = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $parse_url['host'] . '-' . $user_key . '*' . $parse_url['path'];

		/**
		 * Fires before all caching files are deleted for a specific user
		 *
		 * @since 2.6.12
		 *
		 * @param int     $user_id  The path of home cache file.
		 * @param string  $lang     The language code.
		*/
		do_action( 'before_rocket_clean_user', $user_id, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		// Delete cache domain files.
		$dirs = glob( $root . '*', GLOB_NOSORT );
		if ( $dirs ) {
			foreach ( $dirs as $dir ) {
				rocket_rrmdir( $dir, get_rocket_i18n_to_preserve( $lang ) );
			}
		}

		/**
		 * Fires after all caching files are deleted for a specific user
		 *
		 * @since 2.6.12
		 *
		 * @param int     $user_id  The path of home cache file.
		 * @param string  $lang     The language code.
		*/
		do_action( 'after_rocket_clean_user', $user_id, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
}

/**
 * Remove all caching files in the cache folder
 *
 * @since 2.6.8
 *
 * @return void
 */
function rocket_clean_cache_dir() {
	/**
	 * Fires before deleting all caching files in the cache folder
	 *
	 * @since 2.6.8
	*/
	do_action( 'before_rocket_clean_cache_dir' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	// Delete all caching files.
	$dirs = glob( WP_ROCKET_CACHE_PATH . '*', GLOB_NOSORT );
	if ( $dirs ) {
		foreach ( $dirs as $dir ) {
			rocket_rrmdir( $dir );
		}
	}

	/**
	 * Fires after deleting all caching files in the cache folder
	 *
	 * @since 2.6.8
	*/
	do_action( 'after_rocket_clean_cache_dir' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Remove a single file or a folder recursively.
 *
 * @since 3.5.3 Replaces glob and optimizes.
 * @since 1.0
 * @since 3.5.3 Bails if given dir should be preserved; replaces glob; optimizes.
 *
 * @param string                    $dir              File/Directory to delete.
 * @param array                     $dirs_to_preserve Optional. Dirs that should not be deleted.
 * @param WP_Filesystem_Direct|null $filesystem       Optional. Instance of filesystem handler.
 */
function rocket_rrmdir( $dir, array $dirs_to_preserve = [], $filesystem = null ) {
	$dir = untrailingslashit( $dir );

	if ( empty( $filesystem ) ) {
		$filesystem = rocket_direct_filesystem();
	}

	/**
	 * Fires before a file/directory cache is deleted
	 *
	 * @since 1.1.0
	 *
	 * @param string $dir              File/Directory to delete.
	 * @param array  $dirs_to_preserve Directories that should not be deleted.
	 */
	do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	// Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
	$nginx_mobile_detect_file = $dir . '/.mobile-active';

	if ( $filesystem->is_dir( $dir ) && $filesystem->exists( $nginx_mobile_detect_file ) ) {
		$filesystem->delete( $nginx_mobile_detect_file );
	}

	// Remove the hidden empty file for webp.
	$nowebp_detect_file = $dir . '/.no-webp';

	if ( $filesystem->is_dir( $dir ) && $filesystem->exists( $nowebp_detect_file ) ) {
		$filesystem->delete( $nowebp_detect_file );
	}

	if ( ! $filesystem->is_dir( $dir ) ) {
		$filesystem->delete( $dir );

		return;
	}

	// Get the directory entries.
	$entries = [];
	try {
		foreach ( new FilesystemIterator( $dir ) as $entry ) {
			$entries[] = $entry->getPathname();
		}
	} catch ( Exception $e ) { // phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		// No action required, as logging not enabled.
	}

	// Exclude directories to preserve from the entries.
	if ( ! empty( $dirs_to_preserve ) && ! empty( $entries ) ) {
		$keys = [];
		foreach ( $dirs_to_preserve as $dir_to_preserve ) {
			$matches = preg_grep( "#^$dir_to_preserve$#", $entries );
			$keys[]  = reset( $matches );
		}

		if ( ! empty( $keys ) ) {
			$keys = array_filter( $keys );
			if ( ! empty( $keys ) ) {
				$entries = array_diff( $entries, $keys );
			}
		}
	}

	foreach ( $entries as $entry ) {
		// If not a directory, delete it.
		if ( ! $filesystem->is_dir( $entry ) ) {
			$filesystem->delete( $entry );
		} else {
			rocket_rrmdir( $entry, $dirs_to_preserve, $filesystem );
		}
	}

	$filesystem->delete( $dir );

	/**
	 * Fires after a file/directory cache was deleted
	 *
	 * @since 1.1.0
	 *
	 * @param string $dir              File/Directory to delete.
	 * @param array  $dirs_to_preserve Dirs that should not be deleted.
	 */
	do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/**
 * Instanciate the filesystem class
 *
 * @since 2.10
 *
 * @return WP_Filesystem_Direct WP_Filesystem_Direct instance
 */
function rocket_direct_filesystem() {
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	return new WP_Filesystem_Direct( new StdClass() );
}

/**
 * Directory creation based on WordPress Filesystem
 *
 * @since 1.3.4
 *
 * @param string $dir The path of directory will be created.
 * @return bool
 */
function rocket_mkdir( $dir ) {
	$chmod = rocket_get_filesystem_perms( 'dir' );
	return rocket_direct_filesystem()->mkdir( $dir, $chmod );
}

/**
 * Recursive directory creation based on full path.
 *
 * @since 1.3.4
 *
 * @source wp_mkdir_p() in /wp-includes/functions.php
 *
 * @param string $target path to the directory we want to create.
 * @return bool True if directory is created/exists, false otherwise
 */
function rocket_mkdir_p( $target ) {
	$wrapper = null;

	if ( rocket_is_stream( $target ) ) {
		list( $wrapper, $target ) = explode( '://', $target, 2 );
	}

	// from php.net/mkdir user contributed notes.
	$target = str_replace( '//', '/', $target );

	// Put the wrapper back on the target.
	if ( null !== $wrapper ) {
		$target = $wrapper . '://' . $target;
	}

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = rtrim( $target, '/\\' );
	if ( empty( $target ) ) {
		$target = '/';
	}

	if ( rocket_direct_filesystem()->exists( $target ) ) {
		return rocket_direct_filesystem()->is_dir( $target );
	}

	// Attempting to create the directory may clutter up our display.
	if ( rocket_mkdir( $target ) ) {
		return true;
	} elseif ( rocket_direct_filesystem()->is_dir( dirname( $target ) ) ) {
		return false;
	}

	// If the above failed, attempt to create the parent node, then try again.
	if ( ( '/' !== $target ) && ( rocket_mkdir_p( dirname( $target ) ) ) ) {
		return rocket_mkdir_p( $target );
	}

	return false;
}

/**
 * Test if a given path is a stream URL.
 *
 * @since 3.5.3
 *
 * @source wp_is_stream() in /wp-includes/functions.php
 *
 * @param string $path The resource path or URL.
 *
 * @return bool true if the path is a stream URL; else false.
 */
function rocket_is_stream( $path ) {
	$scheme_separator = strpos( $path, '://' );

	if ( false === $scheme_separator ) {
		// $path isn't a stream.
		return false;
	}

	$stream = substr( $path, 0, $scheme_separator );

	return in_array( $stream, stream_get_wrappers(), true );
}

/**
 * File creation based on WordPress Filesystem.
 *
 * @since 1.3.5
 *
 * @param string $file    The path of file will be created.
 * @param string $content The content that will be printed in advanced-cache.php.
 *
 * @return bool true on success; else, false on failure.
 */
function rocket_put_content( $file, $content ) {
	$chmod = rocket_get_filesystem_perms( 'file' );
	return rocket_direct_filesystem()->put_contents( $file, $content, $chmod );
}

/**
 * Get the permissions to apply to files and folders.
 *
 * Reminder:
 * `$perm = fileperms( $file );`
 *
 *  WHAT                                         | TYPE   | FILE   | FOLDER |
 * ----------------------------------------------+--------+--------+--------|
 * `$perm`                                       | int    | 33188  | 16877  |
 * `substr( decoct( $perm ), -4 )`               | string | '0644' | '0755' |
 * `substr( sprintf( '%o', $perm ), -4 )`        | string | '0644' | '0755' |
 * `$perm & 0777`                                | int    | 420    | 493    |
 * `decoct( $perm & 0777 )`                      | string | '644'  | '755'  |
 * `substr( sprintf( '%o', $perm & 0777 ), -4 )` | string | '644'  | '755'  |
 *
 * @since  3.2.4
 *
 * @param  string $type The type: 'dir' or 'file'.
 *
 * @return int          Octal integer.
 */
function rocket_get_filesystem_perms( $type ) {
	static $perms = [];

	if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
		$perms = [];
	}

	// Allow variants.
	switch ( $type ) {
		case 'dir':
		case 'dirs':
		case 'folder':
		case 'folders':
			$type = 'dir';
			break;

		case 'file':
		case 'files':
			$type = 'file';
			break;

		default:
			return 0755;
	}

	if ( isset( $perms[ $type ] ) ) {
		return $perms[ $type ];
	}

	// If the constants are not defined, use fileperms() like WordPress does.
	if ( 'dir' === $type ) {
		$fs_chmod_dir   = (int) rocket_get_constant( 'FS_CHMOD_DIR', 0 );
		$perms[ $type ] = $fs_chmod_dir > 0
			? $fs_chmod_dir
			: fileperms( rocket_get_constant( 'ABSPATH' ) ) & 0777 | 0755;
	} else {
		$fs_chmod_file  = (int) rocket_get_constant( 'FS_CHMOD_FILE', 0 );
		$perms[ $type ] = $fs_chmod_file > 0
			? $fs_chmod_file
			: fileperms( rocket_get_constant( 'ABSPATH' ) . 'index.php' ) & 0777 | 0644;
	}

	return $perms[ $type ];
}

/**
 * Gets Directory files matches regex.
 *
 * @since 3.6.3
 * @access private
 *
 * @param string $dir   Directory to search for files inside it.
 * @param string $regex Regular expression for files need to be searched for.
 *
 * @return array|RegexIterator List of files matches this regular expression.
 */
function _rocket_get_dir_files_by_regex( $dir, $regex ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	try {
		$iterator = new IteratorIterator(
			new FilesystemIterator( $dir )
		);

		return new RegexIterator( $iterator, $regex );
	} catch ( Exception $e ) {
		return [];
	}

}

/**
 * Get the recursive iterator for the cache path.
 *
 * @since  3.5.4
 * @access private
 *
 * @param string $cache_path Path to the cache directory.
 *
 * @return bool|RecursiveIteratorIterator Iterator on success; else false;
 */
function _rocket_get_cache_path_iterator( $cache_path ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	try {
		return new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $cache_path ),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);
	} catch ( Exception $e ) {
		// No logging yet.
		return false;
	}
}

/**
 * Gets the directories for the given URL host from the cache/wp-rocket/ directory or stored memory.
 *
 * @since  3.5.5
 * @access private
 *
 * @param string $url_host   The URL's host.
 * @param string $cache_path Cache's path, e.g. cache/wp-rocket/.
 * @param bool   $hard_reset Optional. When true, resets the static domain directories array and bails out.
 *
 * @return array
 */
function _rocket_get_cache_dirs( $url_host, $cache_path = '', $hard_reset = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	static $domain_dirs = [];

	if ( true === $hard_reset ) {
		$domain_dirs = [];

		return;
	}

	if ( isset( $domain_dirs[ $url_host ] ) ) {
		return $domain_dirs[ $url_host ];
	}

	if ( empty( $cache_path ) ) {
		$cache_path = _rocket_get_wp_rocket_cache_path();
	}

	try {
		$iterator = new IteratorIterator(
			new FilesystemIterator( $cache_path )
		);
	} catch ( Exception $e ) {
		return [];
	}

	$regex = sprintf(
		'/%1$s%2$s(.*)/i',
		_rocket_normalize_path( $cache_path, true ),
		$url_host
	);

	try {
		$entries = new RegexIterator( $iterator, $regex );
	} catch ( Exception $e ) {
		return [];
	}

	$domain_dirs[ $url_host ] = [];
	foreach ( $entries as $entry ) {
		$domain_dirs[ $url_host ][] = $entry->getPathname();
	}

	return $domain_dirs[ $url_host ];
}

/**
 * Normalizes the given filesystem path:
 *  - Windows/IIS-based servers: converts all directory separators to "\\" or, when escaping, to "\\\\".
 *  - Linux-based servers: if $forced is true, uses wp_normalize_path(); else, returns the original path.
 *
 * @since  3.5.5
 * @access private
 *
 * @param string $path   Filesystem path (file or directory) to normalize.
 * @param bool   $escape Optional. When true, escapes the directory separator(s).
 * @param bool   $force  Optional. When true, forces normalizing off non-Windows' paths.
 *
 * @return string
 */
function _rocket_normalize_path( $path, $escape = false, $force = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	if ( _rocket_is_windows_fs( $path ) ) {
		$path = str_replace( '/', '\\', $path );

		return $escape
			? str_replace( '\\', '\\\\', $path )
			: $path;
	}

	if ( $escape ) {
		return str_replace( '/', '\/', $path );
	}

	if ( ! $force ) {
		return $path;
	}

	return wp_normalize_path( $path );
}

/**
 * Checks if the filesystem (fs) is for Windows/IIS server.
 *
 * @since  3.5.5
 * @access private
 *
 * @param bool $hard_reset Optional. When true, resets the memoization.
 *
 * @return bool
 */
function _rocket_is_windows_fs( $hard_reset = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	static $is_windows = null;

	if ( $hard_reset ) {
		$is_windows = null;
	}

	if ( is_null( $is_windows ) ) {
		$is_windows = (
			DIRECTORY_SEPARATOR === '\\'
			&&
			! rocket_get_constant( 'WP_ROCKET_RUNNING_VFS', false )
		);
	}

	return $is_windows;
}

/**
 * Gets the normalized cache path, i.e. normalizes constant "WP_ROCKET_CACHE_PATH".
 *
 * @since  3.5.5
 * @access private
 *
 * @return string
 */
function _rocket_get_wp_rocket_cache_path() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	return _rocket_normalize_path( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
}

/**
 * Gets .php files in a directory as an array of SplFileInfo objects.
 *
 * @since 3.6.3
 *
 * @param string $dir_path Directory to check.
 *
 * @return array .php files in the directory. [...SplFileInfo]
 */
function _rocket_get_php_files_in_dir( $dir_path ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	try {
		$config_dir = new FilesystemIterator( (string) $dir_path );
	} catch ( Exception $e ) {
		return [];
	}
	$files = [];

	foreach ( $config_dir as $file ) {
		if ( $file->isFile() && 'php' === $file->getExtension() ) {
			$files[] = $file;
		}
	}

	return $files;
}

/**
 * Get recursive files matched by regex.
 *
 * @since 3.6.3
 *
 * @param string $regex Regular Expression to be applied.
 *
 * @return array|RegexIterator List of files which match the regular expression (SplFileInfo).
 */
function _rocket_get_recursive_dir_files_by_regex( $regex ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	try {
		$cache_path = _rocket_get_wp_rocket_cache_path();
		$iterator   = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $cache_path, FilesystemIterator::SKIP_DOTS )
		);
		return new RegexIterator( $iterator, $regex, RecursiveRegexIterator::MATCH );
	} catch ( Exception $e ) {
		return [];
	}
}
