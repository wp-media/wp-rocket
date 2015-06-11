<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Generate the content of advanced-cache.php file
 *
 * @since 2.1 	Add filter rocket_advanced_cache_file
 * @since 2.0.3
 *
 * @return 	string 	$buffer The content of avanced-cache.php file
 */
function get_rocket_advanced_cache_file()
{
	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";

	// Add a constant to be sure this is our file
	$buffer .= 'define( \'WP_ROCKET_ADVANCED_CACHE\', true );' . "\n";

	// Get cache path
	$buffer .= '$rocket_cache_path = \'' . WP_ROCKET_CACHE_PATH . '\';' . "\n";

	// Get config path
	$buffer .= '$rocket_config_path = \'' . WP_ROCKET_CONFIG_PATH . '\';' . "\n\n";
	
	// Include the process file in buffer
	$buffer .= 'if ( file_exists( \''. WP_ROCKET_FRONT_PATH . 'process.php' . '\' ) ) {' . "\n";
		$buffer .= "\t" . 'include( \''. WP_ROCKET_FRONT_PATH . 'process.php' . '\' );' . "\n";
	$buffer .= '} else {' . "\n";
		// Add a constant to provent include issue
		$buffer .= "\t" . 'define( \'WP_ROCKET_ADVANCED_CACHE_PROBLEM\', true );' . "\n";
	$buffer .= '}';
	
	/**
	 * Filter the content of advanced-cache.php file
	 *
	 * @since 2.1
	 *
	 * @param string $buffer The content that will be printed in advanced-cache.php
	*/
	$buffer = apply_filters( 'rocket_advanced_cache_file', $buffer );

	return $buffer;
}

/**
 * Create advanced-cache.php file
 *
 * @since 2.0
 *
 * @return void
 */
function rocket_generate_advanced_cache_file()
{
	$buffer  = get_rocket_advanced_cache_file();
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', $buffer );
}

/**
 * Generates the configuration file for the current domain based on the values ​​of options
 *
 * @since 2.0
 *
 * @return array Names of all config files & The content that will be printed
 */
function get_rocket_config_file()
{
	$options = get_option( WP_ROCKET_SLUG );
	if( ! $options ) {
		return;
	}

	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";
	
	if ( apply_filters( 'rocket_override_min_documentRoot', false ) ) {
		$buffer .= '$min_documentRoot = \'' . ABSPATH . '\';' . "\n";
	}
		
	$buffer .= '$rocket_cookie_hash = \'' . COOKIEHASH . '\'' . ";\n";

	foreach ( $options as $option => $value ) {

		if ( $option == 'cache_ssl' || $option == 'cache_mobile' || $option == 'secret_cache_key' ) {
			$buffer .= '$rocket_' . $option . ' = \'' . $value . '\';' . "\n";
		}

		if ( $option == 'cache_reject_uri' ) {
			$buffer .= '$rocket_' . $option . ' = \'' . get_rocket_cache_reject_uri() . '\';' . "\n";
		}
		
		if ( $option == 'cache_query_strings' ) {
			$buffer .= '$rocket_' . $option . ' = ' . var_export( get_rocket_cache_query_string(), true ) . ';' . "\n";
		}

		if ( $option == 'cache_reject_cookies' ) {
			$cookies = get_rocket_cache_reject_cookies();

			if ( get_rocket_option( 'cache_logged_user' ) ) {
				$logged_in_cookie = str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE );
				$cookies = str_replace( $logged_in_cookie . '|', '', $cookies );
				$cookies = trim( $cookies, '|' );
			}

			$buffer .= '$rocket_' . $option . ' = \'' . $cookies . '\';' . "\n";
		}
		
		if ( $option == 'cache_reject_ua' ) {
			$buffer .= '$rocket_' . $option . ' = \'' . get_rocket_cache_reject_ua() . '\';' . "\n";
		}
	}

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', false ) ) {
		$buffer .= '$rocket_url_no_dots = \'1\';';
	}

	$config_files_path = array();
	$urls              = array( home_url() );

	// Check if a translation plugin is activated and this configuration is in subdomain
	if ( $subdomains = get_rocket_i18n_subdomains() ) {
		$urls = $subdomains;
	}

	foreach ( $urls as $url ) {
		list( $host, $path ) = get_rocket_parse_url( rtrim( $url, '/' ) );

		if ( ! isset( $path ) ) {
			$config_files_path[] = WP_ROCKET_CONFIG_PATH . strtolower( $host ) . '.php';
		} else {
			$config_files_path[] = WP_ROCKET_CONFIG_PATH . strtolower( $host ) . str_replace( '/', '.', rtrim( $path, '/' ) ) . '.php';
		}
	}
	
	/**
	 * Filter the content of all config files
	 *
	 * @since 2.1
	 *
	 * @param string $buffer The content that will be printed
	 * @param array $config_files_path 	Names of all config files
	*/
	$buffer = apply_filters( 'rocket_config_file', $buffer, $config_files_path );

	return array( $config_files_path, $buffer );
}

/**
 * Create the current config domain file
 * For example, if home_url() return example.com, the config domain file will be in /config/example.com
 *
 * @since 2.0
 *
 * @return void
 */
function rocket_generate_config_file()
{
	list( $config_files_path, $buffer ) = get_rocket_config_file();

	if ( count( $config_files_path ) ) {
		foreach ( $config_files_path as $file ) {
			rocket_put_content( $file , $buffer );
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
function rocket_delete_config_file()
{
	list( $config_files_path ) = get_rocket_config_file();
	foreach( $config_files_path as $config_file ) {
		@unlink( $config_file );
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
	// Create cache folder if not exist
    if ( ! is_dir( WP_ROCKET_CACHE_PATH ) ) {
	   rocket_mkdir_p( WP_ROCKET_CACHE_PATH );
    }

	// Create minify cache folder if not exist
    if ( ! is_dir( WP_ROCKET_MINIFY_CACHE_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_MINIFY_CACHE_PATH );
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
	// Create config domain folder if not exist
    if ( ! is_dir( WP_ROCKET_CONFIG_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CONFIG_PATH );
    }
}

/**
 * Added or set the value of the WP_CACHE constant
 *
 * @since 2.0
 *
 * @param bool $turn_it_on The value of WP_CACHE constant
 * @return void
 */
function set_rocket_wp_cache_define( $turn_it_on )
{
	// If WP_CACHE is already define, return to get a coffee
	if( ! rocket_valid_key() || ( $turn_it_on && defined( 'WP_CACHE' ) && WP_CACHE ) ) {
		return;
	}

	// Get path of the config file
	$config_file_path = rocket_find_wpconfig_path();
    if ( ! $config_file_path ) {
		return;
    }

	// Get content of the config file
	$config_file = file( $config_file_path );

	// Get the value of WP_CACHE constant
	$turn_it_on = $turn_it_on ? 'true' : 'false';

	/**
	 * Filter allow to change the value of WP_CACHE constant
	 *
	 * @since 2.1
	 *
	 * @param string $turn_it_on The value of WP_CACHE constant
	*/
	apply_filters( 'set_rocket_wp_cache_define', $turn_it_on );

	// Lets find out if the constant WP_CACHE is defined or not
	$is_wp_cache_exist = false;

	// Get WP_CACHE constant define
	$constant = "define('WP_CACHE', $turn_it_on); // Added by WP Rocket". "\r\n";

	foreach ( $config_file as &$line ) {
		if ( ! preg_match( '/^define\(\s*\'([A-Z_]+)\',(.*)\)/', $line, $match ) ) {
			continue;
		}

		if ( $match[1] == 'WP_CACHE' ) {
			$is_wp_cache_exist = true;
			$line = $constant;
		}
	}
	unset( $line );

	// If the constant does not exist, create it
	if ( ! $is_wp_cache_exist ) {
		array_shift( $config_file );
		array_unshift( $config_file, "<?php\r\n", $constant );
	}

	// Insert the constant in wp-config.php file
	$handle = @fopen( $config_file_path, 'w' );
	foreach( $config_file as $line ) {
		@fwrite( $handle, $line );
	}

	@fclose( $handle );

	// Update the writing permissions of wp-config.php file
	$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
	@chmod( $config_file_path, $chmod );
}

/**
 * Delete all minify cache files
 *
 * @since 2.1
 *
 * @param string $ext (default: array('js','css') File extensions to minify
 * @return void
 */
function rocket_clean_minify( $ext = array( 'js','css' ) )
{
	/**
	 * Fires before the minify cache files are deleted
	 *
	 * @since 2.1
	 *
	 * @param string $ext File extensions to minify
	*/
	do_action( 'before_rocket_clean_minify', $ext );

	$files = @glob( WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/*.{' . implode( ',', (array)$ext ) . '}', GLOB_BRACE|GLOB_NOSORT );
	@array_map( 'unlink' , $files );

	/**
	 * Fires after the minify cache files was deleted
	 *
	 * @since 2.1
	 *
	 * @param string $ext File extensions to minify
	*/
	do_action( 'after_rocket_clean_minify', $ext );
}

/**
 * Delete one or several cache files
 *
 * @since 2.0 	Delete cache files for all users
 * @since 1.1.0 Add filter rocket_clean_files
 * @since 1.0
 *
 * @param string|array $urls URLs of cache files to be deleted
 * @return void
 */
function rocket_clean_files( $urls )
{
	if ( is_string( $urls ) ) {
		$urls = (array) $urls;
	}

	/**
	 * Filter URLs that the cache file to be deleted
	 *
	 * @since 1.1.0
	 * @param array URLs that will be returned.
	*/
	$urls = apply_filters( 'rocket_clean_files', $urls );
	$urls = array_filter( $urls );

    foreach ( $urls as $url ) {
		/**
		 * Fires before the cache file is deleted
		 *
		 * @since 1.0
		 *
		 * @param string $url The URL that the cache file to be deleted
		*/
		do_action( 'before_rocket_clean_file', $url );

		/** This filter is documented in inc/front/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$url = str_replace( '.' , '_', $url );
		}

		if ( $dirs = glob( WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( $url ), GLOB_NOSORT ) ) {
			foreach( $dirs as $dir ) {
				rocket_rrmdir( $dir );
			}
		}

		/**
		 * Fires after the cache file is deleted
		 *
		 * @since 1.0
		 *
		 * @param string $url The URL that the cache file was deleted
		*/
		do_action( 'after_rocket_clean_file', $url );
	}
}

/**
 * Remove the home cache file and pagination
 *
 * $since 2.2 Add $lang argument
 * @since 2.0 Delete cache files for all users
 * @since 1.0
 *
 * @param string $lang (default: '') The language code
 * @return void
 */
function rocket_clean_home( $lang = '' )
{
	list( $host, $path ) = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', false ) ) {
		$host = str_replace( '.' , '_', $host );
	}

	$root = WP_ROCKET_CACHE_PATH . $host . '*' . rtrim( $path, '/' );

	/**
	 * Fires before the home cache file is deleted
	 *
	 * @since 1.0
	 *
	 * @param string $root The path of home cache file
	 * @param string $lang The current lang to purge
	*/
	do_action( 'before_rocket_clean_home', $root, $lang );

	// Delete homepage
	if ( $files = glob( $root . '/{index,index-https}.{html,html_gzip}', GLOB_BRACE|GLOB_NOSORT ) ) {
		foreach ( $files as $file ) {
			@unlink( $file );
		}
	}

	// Delete homepage pagination
	if ( $dirs = glob( $root . '*/' . $GLOBALS['wp_rewrite']->pagination_base, GLOB_NOSORT ) ) {
		foreach ( $dirs as $dir ) {
			rocket_rrmdir( $dir );
		}
	}

	/**
	 * Fires after the home cache file was deleted
	 *
	 * @since 1.0
	 *
	 * @param string $root The path of home cache file
	 * @param string $lang The current lang to purge
	*/
    do_action( 'after_rocket_clean_home', $root, $lang );
}

/**
 * Remove all cache files of the domain
 *
 * @since 2.0 Delete domain cache files for all users
 * @since 1.0
 *
 * @param string $lang (default: '') The language code
 * @return void
 */
function rocket_clean_domain( $lang = '' )
{
	$urls = ( ! $lang ) ? get_rocket_i18n_uri() : get_rocket_i18n_home_url( $lang );
	$urls = (array) $urls;
	
	foreach ( $urls as $url ) {
		list( $host, $path ) = get_rocket_parse_url( $url );

		/** This filter is documented in inc/front/htaccess.php */
		if( apply_filters( 'rocket_url_no_dots', false ) ) {
			$host = str_replace( '.' , '_', $host );
		}

		$root = WP_ROCKET_CACHE_PATH . $host . '*' . $path;

		/**
		 * Fires before all cache files are deleted
		 *
		 * @since 1.0
		 *
		 * @param string $root The path of home cache file
		 * @param string $lang The current lang to purge
		*/
		do_action( 'before_rocket_clean_domain', $root, $lang );

		// Delete cache domain files
		if( $dirs = glob( $root . '*', GLOB_NOSORT ) ) {
			foreach ( $dirs as $dir ) {
				rocket_rrmdir( $dir, get_rocket_i18n_to_preserve( $lang ) );
			}
		}

		/**
		 * Fires after all cache files was deleted
		 *
		 * @since 1.0
		 *
		 * @param string $root The path of home cache file
		 * @param string $lang The current lang to purge
		*/
	    do_action( 'after_rocket_clean_domain', $root, $lang );
	}
}

/**
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 * @param string $dir File/Directory to delete
 * @param array $dirs_to_preserve (default: array()) Dirs that should not be deleted
 * @return void
 */
function rocket_rrmdir( $dir, $dirs_to_preserve = array() )
{
	$dir = rtrim( $dir, '/' );

	/**
	 * Fires after a file/directory cache was deleted
	 *
	 * @since 1.1.0
	 *
	 * @param string $dir File/Directory to delete
	 * @param array $dirs_to_preserve Directories that should not be deleted
	*/
	do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve );

	if ( ! is_dir( $dir ) ) {
		@unlink( $dir );
		return;
	};

    if ( $dirs = glob( $dir . '/*', GLOB_NOSORT ) ) {

		$keys = array();
		foreach( $dirs_to_preserve as $dir_to_preserve ) {
			$matches = preg_grep( "#^$dir_to_preserve$#" , $dirs );
			$keys[] = reset( $matches );
		}

		$dirs = array_diff( $dirs, array_filter( $keys ) );
		foreach ( $dirs as $dir ) {
			if ( is_dir( $dir ) ) {
				rocket_rrmdir( $dir, $dirs_to_preserve );
			} else {
				@unlink( $dir );
			}
		}
	}

	@rmdir($dir);

	/**
	 * Fires before a file/directory cache was deleted
	 *
	 * @since 1.1.0
	 *
	 * @param string $dir File/Directory to delete
	 * @param array $dirs_to_preserve Dirs that should not be deleted
	*/
	do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve );
}

/**
 * Directory creation based on WordPress Filesystem
 *
 * @since 1.3.4
 *
 * @param string $dir The path of directory will be created
 * @return bool
 */
function rocket_mkdir( $dir )
{
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

	$chmod = defined( 'FS_CHMOD_DIR' ) ? FS_CHMOD_DIR : ( fileperms( WP_CONTENT_DIR ) & 0777 | 0755 );
	return $direct_filesystem->mkdir( $dir, $chmod );
}

/**
 * Recursive directory creation based on full path.
 *
 * @since 1.3.4
 *
 * @source wp_mkdir_p() in /wp-includes/functions.php
 */
function rocket_mkdir_p( $target )
{
	// from php.net/mkdir user contributed notes
	$target = str_replace( '//', '/', $target );

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = rtrim($target, '/'); // Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
	if ( empty($target) ) {
		$target = '/';
	}

	if ( file_exists( $target ) ) {
		return @is_dir( $target );
	}

	// Attempting to create the directory may clutter up our display.
	if ( rocket_mkdir( $target ) ) {
		return true;
	} elseif ( is_dir( dirname( $target ) ) ) {
		return false;
	}

	// If the above failed, attempt to create the parent node, then try again.
	if ( ( $target != '/' ) && ( rocket_mkdir_p( dirname( $target ) ) ) ) {
		return rocket_mkdir_p( $target );
	}

	return false;
}

/**
 * File creation based on WordPress Filesystem
 *
 * @since 1.3.5
 *
 * @param string $file 	  The path of file will be created
 * @param string $content The content that will be printed in advanced-cache.php
 * @return bool
 */
function rocket_put_content( $file, $content )
{
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

	$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
	return $direct_filesystem->put_contents( $file, $content, $chmod );
}

/**
 * Try to find the correct wp-config.php file, support one level up in filetree
 *
 * @since 2.1
 *
 * @return string|bool The path of wp-config.php file or false
 */
function rocket_find_wpconfig_path()
{
	$config_file = get_home_path() . 'wp-config.php';
	$config_file_alt = dirname( get_home_path() ) . '/wp-config.php';

	if ( file_exists( $config_file ) && is_writable( $config_file ) ) {
		return $config_file;
	} elseif ( @file_exists( $config_file_alt ) && is_writable( $config_file_alt ) && !file_exists( dirname( get_home_path() ) . '/wp-settings.php' ) ) {
		return $config_file_alt;
	}

	// No writable file found
	return false;
}

/**
 * Get WP Rocket footprint
 *
 * @since 2.0
 *
 * @param bool $debug (default: true) If true, adds the date of generation cache file
 * @return string The footprint that will be printed
 */
function get_rocket_footprint( $debug = true )
{
	$footprint = ! rocket_is_white_label() ?
					"\n" . '<!-- This website is like a Rocket, isn\'t it ? Performance optimized by WP Rocket. Learn more: http://wp-rocket.me' :
					"\n" . '<!-- Cached page for great performance';
	if ( $debug ) {
		$footprint .= ' - Debug: cached@' . time();
	}
	$footprint .= ' -->';
	return $footprint;
}