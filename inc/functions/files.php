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
function get_rocket_advanced_cache_file() {
	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";

	// Add a constant to be sure this is our file
	$buffer .= 'define( \'WP_ROCKET_ADVANCED_CACHE\', true );' . "\n";

	// Get cache path
	$buffer .= '$rocket_cache_path = \'' . WP_ROCKET_CACHE_PATH . '\';' . "\n";

	// Get config path
	$buffer .= '$rocket_config_path = \'' . WP_ROCKET_CONFIG_PATH . '\';' . "\n\n";
	
	// Include the Mobile Detect class if we have to create a different caching file for mobile
	if ( is_rocket_generate_caching_mobile_files() ) {
		$buffer .= 'if ( file_exists( \''. WP_ROCKET_VENDORS_PATH . 'Mobile_Detect.php' . '\' ) ) {' . "\n";
		$buffer .= "\t" . 'include( \''. WP_ROCKET_VENDORS_PATH . 'Mobile_Detect.php' . '\' );' . "\n";
	$buffer .= '}' . "\n";
	}
	
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
function rocket_generate_advanced_cache_file() {
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', get_rocket_advanced_cache_file() );
}

/**
 * Generates the configuration file for the current domain based on the values ​​of options
 *
 * @since 2.0
 *
 * @return array Names of all config files & The content that will be printed
 */
function get_rocket_config_file() {
	$options = get_option( WP_ROCKET_SLUG );
	
	if ( ! $options ) {
		return;
	}

	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";

	if ( apply_filters( 'rocket_override_min_documentRoot', false ) ) {
		/**
		 * Filter the Document Root path to use during the minification
		 *
		 * @since 2.7
		 *
		 * @param string The Document Root path
		*/
		$min_documentRoot = apply_filters( 'rocket_min_documentRoot', ABSPATH );
		
		$buffer .= '$min_documentRoot = \'' . $min_documentRoot . '\';' . "\n";
	}

    if ( apply_filters( 'rocket_override_min_cachepath', false ) ) {
		/**
		 * Filter the temp directory path to use during the minification
		 *
		 * @since 2.8.3
		 *
		 * @param string The temp path, empty to leave Minify guessing it automatically
		*/
		$min_cachePath = apply_filters( 'rocket_min_cachePath', '' );
		
		$buffer .= '$min_cachePath = \'' . $min_cachePath . '\';' . "\n";
	}

    /**
     * Filters the preservation of the CSS comments during minification
     *
     * @author Remy Perona
     * @since 2.9
     *
     * @param bool False to not preserve the comments, true to preserve
    */
    if ( apply_filters( 'rocket_minification_preserve_css_comments', false ) ) {
        $buffer .= '$min_preserve_css_comments = true;' . "\n";
    }

	$buffer .= '$rocket_cookie_hash = \'' . COOKIEHASH . '\'' . ";\n";

	foreach ( $options as $option => $value ) {	
		if ( $option == 'cache_ssl' || $option == 'cache_mobile' || $option == 'do_caching_mobile_files' || $option == 'secret_cache_key' || $option == 'common_cache_logged_users' ) {
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
	
	$buffer .= '$rocket_cache_mandatory_cookies = ' . var_export( get_rocket_cache_mandatory_cookies(), true ) . ';'  . "\n";
	
	$buffer .= '$rocket_cache_dynamic_cookies = ' . var_export( get_rocket_cache_dynamic_cookies(), true ) . ';'  . "\n";
	
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
		list( $host, $path ) = get_rocket_parse_url( untrailingslashit( $url ) );
		$path = ( ! empty( $path ) ) ? str_replace( '/', '.', untrailingslashit( $path ) ) : '';
		$config_files_path[] = WP_ROCKET_CONFIG_PATH . strtolower( $host ) . $path . '.php';
	}

	/**
	 * Filter all config files path
	 *
	 * @since 2.6.5
	 *
	 * @param array $config_files_path 	Path of all config files
	*/
	$config_files_path = apply_filters( 'rocket_config_files_path', $config_files_path );

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
function rocket_generate_config_file() {
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
function rocket_delete_config_file() {
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

    // Create busting cache folder if not exist
    if ( ! is_dir( WP_ROCKET_CACHE_BUSTING_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CACHE_BUSTING_PATH );
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
function set_rocket_wp_cache_define( $turn_it_on ) {
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
 * @param  string|array $extensions (default: array('js','css') File extensions to minify
 * @return void
 */
function rocket_clean_minify( $extensions = array( 'js','css' ) ) {
	$blog_id    = get_current_blog_id();
	$extensions = is_string( $extensions ) ? (array) $extensions : $extensions;
	
	foreach ( $extensions as $ext ) {
		/**
		 * Fires before the minify cache files are deleted
		 *
		 * @since 2.1
		 *
		 * @param string $ext File extensions to minify
		*/
		do_action( 'before_rocket_clean_minify', $ext );
		
		if ( $files = @glob( WP_ROCKET_MINIFY_CACHE_PATH . $blog_id . '/*.' . $ext, GLOB_NOSORT ) ) {
			foreach ( $files as $file ) { // no array map to use @
				@unlink( $file );
			}
		}
		
		/**
		 * Fires after the minify cache files was deleted
		 *
		 * @since 2.1
		 *
		 * @param string $ext File extensions to minify
		*/
		do_action( 'after_rocket_clean_minify', $ext );	
	}
}

/**
 * Delete all cache busting files
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param  string|array $extensions (default: array('js','css') File extensions to clean
 * @return void
 */
function rocket_clean_cache_busting( $extensions = array( 'js','css' ) ) {
	$blog_id    = get_current_blog_id();
	$extensions = is_string( $extensions ) ? (array) $extensions : $extensions;
	
	foreach ( $extensions as $ext ) {
		/**
		 * Fires before the cache busting files are deleted
		 *
		 * @since 2.9
		 *
		 * @param string $ext File extensions to minify
		*/
		do_action( 'before_rocket_clean_cache_busting', $ext );
		
		if ( $files = @glob( WP_ROCKET_CACHE_BUSTING_PATH . $blog_id . '/*.' . $ext, GLOB_NOSORT ) ) {
			foreach ( $files as $file ) { // no array map to use @
				@unlink( $file );
			}
		}
		
		/**
		 * Fires after the cache busting files was deleted
		 *
		 * @since 2.9
		 *
		 * @param string $ext File extensions to clean
		*/
		do_action( 'after_rocket_clean_cache_busting', $ext );	
	}
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
function rocket_clean_files( $urls ) {
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
function rocket_clean_home( $lang = '' ) {
	list( $host, $path ) = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );

	/** This filter is documented in inc/front/htaccess.php */
	if ( apply_filters( 'rocket_url_no_dots', false ) ) {
		$host = str_replace( '.' , '_', $host );
	}

	$root = WP_ROCKET_CACHE_PATH . $host . '*' . untrailingslashit( $path );
	
	/**
	 * Filter the homepage caching folder root
	 *
	 * @since 2.6.5
	 * @param array 	$root The root that will be returned
	 * @param string 	$host The website host
	 * @param string 	$path The website path
	*/
	$root = apply_filters( 'rocket_clean_home_root', $root, $host, $path );
		
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
	if ( $files = glob( $root . '/{index,index-*}.{html,html_gzip}', GLOB_BRACE|GLOB_NOSORT ) ) {
		foreach ( $files as $file ) { // no array map to use @	
			@unlink( $file );
		}
	}

	// Delete homepage pagination
	if ( $dirs = glob( $root . '*/' . $GLOBALS['wp_rewrite']->pagination_base, GLOB_NOSORT ) ) {
		foreach ( $dirs as $dir ) {
			rocket_rrmdir( $dir );
		}
	}

    // Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration
    if ( $nginx_mobile_detect_files = glob( $root . '/.mobile-active', GLOB_BRACE|GLOB_NOSORT ) ) {
        foreach ( $nginx_mobile_detect_files as $nginx_mobile_detect_file ) { // no array map to use @
			@unlink( $nginx_mobile_detect_file );
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
 * Remove the home cache feed
 *
 * @since 2.7
 *
 * @return void
 */
function rocket_clean_home_feeds() {

    $urls = array();
	$urls[] = get_feed_link();
	$urls[] = get_feed_link( 'comments_' );

    /**
	 * Filter the home feeds urls
	 *
	 * @since 2.7
	 * @param array 	$urls The urls of the home feeds
	*/
    $urls = apply_filters( 'rocket_clean_home_feeds', $urls );

    /**
	 * Fires before the home feeds cache is deleted
	 *
	 * @since 2.7
	 *
	 * @param array $urls The urls of the home feeds
	*/
    do_action( 'before_rocket_clean_home_feeds', $urls );

    rocket_clean_files( $urls );

    /**
	 * Fires after the home feeds cache was deleted
	 *
	 * @since 2.7
	 *
	 * @param array $urls The urls of the home feeds
	*/
    do_action( 'after_rocket_clean_home_feeds', $urls );
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
function rocket_clean_domain( $lang = '' ) {
	$urls = ( ! $lang || is_object( $lang ) || is_array( $lang ) ) ? get_rocket_i18n_uri() : get_rocket_i18n_home_url( $lang );
	$urls = (array) $urls;

	/**
	 * Filter URLs to delete all caching files from a domain
	 *
	 * @since 2.6.4
	 * @param array 	URLs that will be returned
	 * @param string 	The language code
	*/
	$urls = apply_filters( 'rocket_clean_domain_urls', $urls, $lang );
	$urls = array_filter( $urls );

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
		 * @param string $url  The home url
		*/
		do_action( 'before_rocket_clean_domain', $root, $lang, $url );

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
		 * @param string $url  The home url
		*/
	    do_action( 'after_rocket_clean_domain', $root, $lang, $url );
	}
}

/**
 * Delete the caching files of a specific term
 *
 * $since 2.6.8
 *
 * @param int 	 $term_id 		The term ID
 * @param string $taxonomy_name The taxonomy slug
 * @return void
 */
function rocket_clean_term( $term_id, $taxonomy_slug ) {	
	$purge_urls = array();
	
	// Get all term infos
	$term = get_term_by( 'id', $term_id, $taxonomy_slug );
	
	// Get the term language
	$lang = false;
	
	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && ! rocket_is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
		$lang = $GLOBALS['sitepress']->get_language_for_element( $term_id, 'tax_' . $taxonomy_slug );

	// Polylang
	} else if ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
		$lang = pll_get_term_language( $term_id );
	}
	
	// Get permalink
	$permalink = get_term_link( $term, $taxonomy_slug );
	
	// Add permalink
	if( parse_url( $permalink, PHP_URL_PATH ) != '/' ) {
		array_push( $purge_urls, $permalink );	
	}
	
	/**
	 * Fires before deleted caching files related with the term
	 *
	 * @since 2.6.8
	 * @param obj 	 $term 		 The term object
	 * @param array  $purge_urls URLs cache files to remove
	 * @param string $lang 		 The term language
	*/
	do_action( 'before_rocket_clean_term', $term, $purge_urls, $lang );

	/**
	 * Filter URLs cache files to remove
	 *
	 * @since 2.6.8
	 * @param array $purge_urls List of URLs cache files to remove
	 * @param obj 	$term 		The term object
	*/
	$purge_urls = apply_filters( 'rocket_term_purge_urls', $purge_urls, $term );
	
	// Purge all files
	rocket_clean_files( $purge_urls );

	// Never forget to purge homepage and their pagination
	rocket_clean_home( $lang );
	
	/**
	 * Fires before deleted caching files related with the term
	 *
	 * @since 2.6.8
	 * @param obj 	 $term 		 The term object
	 * @param array  $purge_urls URLs cache files to remove
	 * @param string $lang 		 The term language
	*/
	do_action( 'after_rocket_clean_term', $term, $purge_urls, $lang );
}

/**
 * Delete the caching files of a specific user
 *
 * $since 2.6.12
 *
 * @param int 	 $user_id  The user ID
 * @param string $lang 	   The language code
 * @return void
 */
function rocket_clean_user( $user_id, $lang = '' ) {
	$urls = ( ! $lang || is_object( $lang ) ) ? get_rocket_i18n_uri() : get_rocket_i18n_home_url( $lang );
	$urls = (array) $urls;

	/** This filter is documented in inc/functions/files.php */
	$urls = apply_filters( 'rocket_clean_domain_urls', $urls, $lang );
	$urls = array_filter( $urls );
	
	if ( ! $user = get_user_by( 'id', $user_id ) ) {
		return;
	}
	
	$user_key = $user->user_login . '-' . get_rocket_option( 'secret_cache_key' );
	
	foreach ( $urls as $url ) {
		list( $host, $path ) = get_rocket_parse_url( $url );

		/** This filter is documented in inc/front/htaccess.php */
		if( apply_filters( 'rocket_url_no_dots', false ) ) {
			$host = str_replace( '.' , '_', $host );
		}

		$root = WP_ROCKET_CACHE_PATH . $host . '-' . $user_key . '*' . $path;

		/**
		 * Fires before all caching files are deleted for a specific user
		 *
		 * @since 2.6.12
		 *
		 * @param int 	  $user_id  The path of home cache file
		 * @param string  $lang 	The language code
		*/
		do_action( 'before_rocket_clean_user', $user_id, $lang );

		// Delete cache domain files
		if( $dirs = glob( $root . '*', GLOB_NOSORT ) ) {
			foreach ( $dirs as $dir ) {
				rocket_rrmdir( $dir, get_rocket_i18n_to_preserve( $lang ) );
			}
		}
		
		/**
		 * Fires after all caching files are deleted for a specific user
		 *
		 * @since 2.6.12
		 *
		 * @param int 	  $user_id  The path of home cache file
		 * @param string  $lang 	The language code
		*/
	    do_action( 'after_rocket_clean_user', $user_id, $lang );
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
	do_action( 'before_rocket_clean_cache_dir' );
	
	// Delete all caching files
	if( $dirs = glob( WP_ROCKET_CACHE_PATH . '*', GLOB_NOSORT ) ) {
		foreach ( $dirs as $dir ) {
			rocket_rrmdir( $dir );
		}
	}
	
	/**
	 * Fires after deleting all caching files in the cache folder
	 *
	 * @since 2.6.8
	*/
    do_action( 'after_rocket_clean_cache_dir' );
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
function rocket_rrmdir( $dir, $dirs_to_preserve = array() ) {
	$dir = untrailingslashit( $dir );

	/**
	 * Fires before a file/directory cache is deleted
	 *
	 * @since 1.1.0
	 *
	 * @param string $dir File/Directory to delete
	 * @param array $dirs_to_preserve Directories that should not be deleted
	*/
	do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve );

    // Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration
    $nginx_mobile_detect_file = $dir . '/.mobile-active';

    if ( is_dir( $dir ) && file_exists( $nginx_mobile_detect_file ) ) {
        @unlink( $nginx_mobile_detect_file );
    }

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
	 * Fires after a file/directory cache was deleted
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
function rocket_mkdir( $dir ) {
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
function rocket_mkdir_p( $target ) {
	// from php.net/mkdir user contributed notes
	$target = str_replace( '//', '/', $target );

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = untrailingslashit( $target );
	if ( empty( $target ) ) {
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
function rocket_put_content( $file, $content ) {
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
function rocket_find_wpconfig_path() {
	$config_file     = ABSPATH . 'wp-config.php';
	$config_file_alt = dirname( ABSPATH ) . '/wp-config.php';

	if ( file_exists( $config_file ) && is_writable( $config_file ) ) {
		return $config_file;
	} elseif ( @file_exists( $config_file_alt ) && is_writable( $config_file_alt ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
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
function get_rocket_footprint( $debug = true ) {
	$footprint = ! rocket_is_white_label() ?
					"\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by WP Rocket. Learn more: https://wp-rocket.me' :
					"\n" . '<!-- Cached page for great performance';
	if ( $debug ) {
		$footprint .= ' - Debug: cached@' . time();
	}
	$footprint .= ' -->';
	return $footprint;
}