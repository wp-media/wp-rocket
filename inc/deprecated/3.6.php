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
class_alias( '\WP_Rocket\Engine\Optimization\IEConditionalSubscriber', '\WP_Rocket\Subscriber\Optimization\IE_Conditionals_Subscriber' );
class_alias( '\WP_Rocket\ThirdParty\Plugins\Smush', '\WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber' );
class_alias( '\WP_Rocket\Engine\Capabilities\Subscriber', '\WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber' );

/**
 * Generate the content of advanced-cache.php file.
 *
 * @since 3.6 deprecated
 * @since 3.5.5 Uses rocket_get_constant() for constants.
 * @since 2.1   Add filter rocket_advanced_cache_file.
 * @since 2.0.3
 *
 * @return  string  $buffer The content of avanced-cache.php file
 */
function get_rocket_advanced_cache_file() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content()' );

	$buffer  = "<?php\n";
	$buffer .= "defined( 'ABSPATH' ) || exit;\n\n";

	// Add a constant to be sure this is our file.
	$buffer .= "define( 'WP_ROCKET_ADVANCED_CACHE', true );\n\n";

	$buffer .= "if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {\n";
	$buffer .= "\tdefine( 'WP_ROCKET_CONFIG_PATH',       WP_CONTENT_DIR . '/wp-rocket-config/' );\n";
	$buffer .= "}\n\n";

	// Include the Mobile Detect class if we have to create a different caching file for mobile.
	if ( is_rocket_generate_caching_mobile_files() ) {
		$vendor_path = rocket_get_constant( 'WP_ROCKET_VENDORS_PATH' );

		$buffer .= "if ( file_exists( '" . $vendor_path . "classes/class-rocket-mobile-detect.php' ) && ! class_exists( 'Rocket_Mobile_Detect' ) ) {\n";
		$buffer .= "\tinclude_once '" . $vendor_path . "classes/class-rocket-mobile-detect.php';\n";
		$buffer .= "}\n\n";
	}

	// Register a class autoloader and include the process file.
	$buffer .= "if ( version_compare( phpversion(), '" . rocket_get_constant( 'WP_ROCKET_PHP_VERSION' ) . "' ) >= 0 ) {\n\n";

	// Class autoloader.
	$autoloader = rocket_direct_filesystem()->get_contents( rocket_get_constant( 'WP_ROCKET_INC_PATH' ) . 'process-autoloader.php' );

	if ( $autoloader ) {
		$autoloader = preg_replace( '@^<\?php\s*@', '', $autoloader );
		$autoloader = str_replace( [ "\n", "\n\t\n" ], [ "\n\t", "\n\n" ], trim( $autoloader ) );
		$autoloader = str_replace( 'WP_ROCKET_PATH', "'" . rocket_get_constant( 'WP_ROCKET_PATH' ) . "'", $autoloader );

		$buffer .= "\t$autoloader\n\n";
	}

	// Initialize the Cache class and process.
	$buffer .= "\t" . 'if ( ! class_exists( \'\WP_Rocket\Buffer\Cache\' ) ) {
		if ( ! defined( \'DONOTROCKETOPTIMIZE\' ) ) {
			define( \'DONOTROCKETOPTIMIZE\', true ); // WPCS: prefix ok.
		}
		return;
	}

	$rocket_config_class = new \WP_Rocket\Buffer\Config(
		[
			\'config_dir_path\' => \'' . rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) . '\',
		]
	);

	( new \WP_Rocket\Buffer\Cache(
		new \WP_Rocket\Buffer\Tests(
			$rocket_config_class
		),
		$rocket_config_class,
		[
			\'cache_dir_path\' => \'' . rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . '\',
		]
	) )->maybe_init_process();' . "\n";
	$buffer .= "} else {\n";
	// Add a constant to provent include issue.
	$buffer .= "\tdefine( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );\n";
	$buffer .= "}\n";

	/**
	 * Filter the content of advanced-cache.php file.
	 *
	 * @since 2.1
	 *
	 * @param string $buffer The content that will be printed in advanced-cache.php.
	*/
	return (string) apply_filters( 'rocket_advanced_cache_file', $buffer );
}

/**
 * This warning is displayed when the advanced-cache.php file isn't writeable
 *
 * @since 3.6 deprecated
 * @since 2.0
 */
function rocket_warning_advanced_cache_permissions() {
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::notice_permissions()' );

	$advanced_cache_file = WP_CONTENT_DIR . '/advanced-cache.php';

	if ( current_user_can( 'rocket_manage_options' )
		&& ! rocket_direct_filesystem()->is_writable( $advanced_cache_file )
		&& ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) || ! WP_ROCKET_ADVANCED_CACHE )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( basename( WP_CONTENT_DIR ) . '/advanced-cache.php' );

		rocket_notice_html(
			[
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => $message,
				'dismiss_button'   => __FUNCTION__,
				'readonly_content' => get_rocket_advanced_cache_file(),
			]
		);
	}
}

/**
 * This warning is displayed when the advanced-cache.php file isn't ours
 *
 * @since 3.6 Deprecated
 * @since 2.2
 */
function rocket_warning_advanced_cache_not_ours() {
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::notice_content_not_ours()' );

	if ( ! ( 'plugins.php' === $GLOBALS['pagenow'] && isset( $_GET['activate'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		&& current_user_can( 'rocket_manage_options' )
		&& ! defined( 'WP_ROCKET_ADVANCED_CACHE' )
		&& ( defined( 'WP_CACHE' ) && WP_CACHE )
		&& get_rocket_option( 'version' ) === WP_ROCKET_VERSION
		&& rocket_valid_key() ) {

			$message = rocket_notice_writing_permissions( basename( WP_CONTENT_DIR ) . '/advanced-cache.php' );

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

/**
 * This warning is displayed when the wp-config.php file isn't writable
 *
 * @since 3.6.1 deprecated
 * @since 2.0
 */
function rocket_warning_wp_config_permissions() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\WPCache::notice_wp_config_permissions()' );
	$config_file = rocket_find_wpconfig_path();

	if ( ! ( 'plugins.php' === $GLOBALS['pagenow'] && isset( $_GET['activate'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		&& current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( $config_file ) && ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( 'wp-config.php' );

		rocket_notice_html(
			[
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => $message,
				'dismiss_button'   => __FUNCTION__,
				'readonly_content' => '/** Enable Cache by ' . WP_ROCKET_PLUGIN_NAME . " */\r\ndefine( 'WP_CACHE', true );\r\n",
			]
		);
	}
}

/**
 * Try to find the correct wp-config.php file, support one level up in file tree.
 *
 * @since 3.6 deprecated
 * @since 2.1
 *
 * @return string|bool The path of wp-config.php file or false if not found.
 */
function rocket_find_wpconfig_path() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\WPCache::find_wpconfig_path()' );
	/**
	 * Filter the wp-config's filename.
	 *
	 * @since 2.11
	 *
	 * @param string $filename The WP Config filename, without the extension.
	 */
	$config_file_name = apply_filters( 'rocket_wp_config_name', 'wp-config' );
	$abspath          = rocket_get_constant( 'ABSPATH' );
	$config_file      = "{$abspath}{$config_file_name}.php";
	$filesystem       = rocket_direct_filesystem();

	if (
		$filesystem->exists( $config_file )
		&&
		$filesystem->is_writable( $config_file )
	) {
		return $config_file;
	}

	$abspath_parent  = dirname( $abspath ) . DIRECTORY_SEPARATOR;
	$config_file_alt = "{$abspath_parent}{$config_file_name}.php";

	if (
		$filesystem->exists( $config_file_alt )
		&&
		$filesystem->is_writable( $config_file_alt )
		&&
		! $filesystem->exists( "{$abspath_parent}wp-settings.php" )
	) {
		return $config_file_alt;
	}

	// No writable file found.
	return false;
}

/**
 * Define WP_CACHE to true if it's not defined yet.
 *
 * @since 3.6.1 deprecated
 * @since 2.6
 */
function rocket_maybe_set_wp_cache_define() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\WPCache::maybe_set_wp_cache()' );

	if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}
}

/**
 * Get all dates archives urls associated to a specific post.
 *
 * @since 3.6.1 deprecated
 * @since 1.0
 *
 * @param int $post_id The post ID.
 *
 * @return array $urls List of dates URLs on success; else, an empty [].
 */
function get_rocket_post_dates_urls( $post_id ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\Purge::purge_dates_archives()' );
	$time = get_the_time( 'Y-m-d', $post_id );
	if ( empty( $time ) ) {
		return [];
	}

	// Extract and prep the year, month, and day.
	$date  = explode( '-', $time );
	$year  = trailingslashit( get_year_link( $date[0] ) );
	$month = trailingslashit( get_month_link( $date[0], $date[1] ) );

	$urls = [
		"{$year}index.html",
		"{$year}index.html_gzip",
		$year . $GLOBALS['wp_rewrite']->pagination_base,
		"{$month}index.html",
		"{$month}index.html_gzip",
		$month . $GLOBALS['wp_rewrite']->pagination_base,
		get_day_link( $date[0], $date[1], $date[2] ),
	];

	/**
	 * Filter the list of dates URLs.
	 *
	 * @since 1.1.0
	 *
	 * @param array $urls List of dates URLs.
	*/
	return (array) apply_filters( 'rocket_post_dates_urls', $urls );
}

/**
 * Added or set the value of the WP_CACHE constant
 *
 * @since 3.6.1 deprecated
 * @since 2.0
 *
 * @param bool $turn_it_on The value of WP_CACHE constant.
 * @return void
 */
function set_rocket_wp_cache_define( $turn_it_on ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\WPCache::set_wp_cache_constant()' );
	// If WP_CACHE is already define, return to get a coffee.
	if ( ! rocket_valid_key() || ( $turn_it_on && defined( 'WP_CACHE' ) && WP_CACHE ) ) {
		return;
	}

	if ( defined( 'IS_PRESSABLE' ) && IS_PRESSABLE ) {
		return;
	}

	// Get path of the config file.
	$config_file_path = rocket_find_wpconfig_path();
	if ( ! $config_file_path ) {
		return;
	}

	$filesystem = rocket_direct_filesystem();

	// Get content of the config file.
	$config_file_contents = $filesystem->get_contents( $config_file_path );

	// Get the value of WP_CACHE constant.
	$turn_it_on = $turn_it_on ? 'true' : 'false';

	/**
	 * Filter allow to change the value of WP_CACHE constant
	 *
	 * @since 2.1
	 *
	 * @param string $turn_it_on The value of WP_CACHE constant.
	*/
	$turn_it_on = apply_filters( 'set_rocket_wp_cache_define', $turn_it_on ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	// Get WP_CACHE constant define.
	$constant = "define('WP_CACHE', $turn_it_on); // Added by WP Rocket";

	// Lets find out if the constant WP_CACHE is defined or not.
	$wp_cache_found = preg_match( '/^define\(\s*\'WP_CACHE\',(.*)\)/m', $config_file_contents, $matches );

	if ( ! $wp_cache_found ) {
		$config_file_contents = preg_replace( '/(<\?php)/i', "<?php\r\n{$constant}\r\n", $config_file_contents );
	} elseif ( ! empty( $matches[1] ) && $matches[1] !== $turn_it_on ) {
		$config_file_contents = preg_replace( '/^define\(\s*\'WP_CACHE\',(.*)\).+/m', $constant, $config_file_contents );
	}

	// Insert the constant in wp-config.php file.
	rocket_put_content( $config_file_path, $config_file_contents );
}

/**
 * Get all terms archives urls associated to a specific post
 *
 * @since 3.6.1 deprecated
 * @since 1.0
 *
 * @param int $post_id The post ID.
 * @return array $urls List of taxonomies URLs
 */
function get_rocket_post_terms_urls( $post_id ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\Engine\Cache\Purge::get_post_terms_urls()' );
	$urls       = [];
	$taxonomies = get_object_taxonomies( get_post_type( $post_id ), 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! $taxonomy->public || 'product_shipping_class' === $taxonomy->name ) {
			continue;
		}

		// Get the terms related to post.
		$terms = get_the_terms( $post_id, $taxonomy->name );

		if ( empty( $terms ) ) {
			continue;
		}
		foreach ( $terms as $term ) {
			$term_url = get_term_link( $term->slug, $taxonomy->name );
			if ( ! is_wp_error( $term_url ) ) {
				$urls[] = $term_url;
			}
			if ( ! is_taxonomy_hierarchical( $taxonomy->name ) ) {
				continue;
			}
			$ancestors = (array) get_ancestors( $term->term_id, $taxonomy->name );
			foreach ( $ancestors as $ancestor ) {
				$ancestor_object = get_term( $ancestor, $taxonomy->name );
				if ( ! $ancestor_object instanceof WP_Term ) {
					continue;
				}
				$ancestor_term_url = get_term_link( $ancestor_object->slug, $taxonomy->name );
				if ( ! is_wp_error( $ancestor_term_url ) ) {
					$urls[] = $ancestor_term_url;
				}
			}
		}
	}

	/**
	 * Filter the list of taxonomies URLs
	 *
	 * @since 1.1.0
	 *
	 * @param array $urls List of taxonomies URLs
	*/
	return apply_filters( 'rocket_post_terms_urls', $urls );
}

/**
 * Rules to serve gzip compressed CSS & JS files if they exists and client accepts gzip
 *
 * @since 3.6.0.3 deprecated
 * @since 3.6.0.2 Update rules used to prevent content encoding issue
 * @since 3.6
 * @author Remy Perona
 *
 * @return string
 */
function rocket_get_compressed_assets_rules() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.0.3' );

	$rules = <<<HTACCESS
<IfModule mod_headers.c>
    RewriteCond %{HTTP:Accept-Encoding} gzip
    RewriteCond %{REQUEST_FILENAME}\.gz -f
    RewriteRule \.(css|js)$ %{REQUEST_URI}.gz [L]

    # Prevent mod_deflate double gzip
	RewriteRule \.gz$ - [E=no-gzip:1]

	<FilesMatch "\.gz$">

        # Serve correct content types
        <IfModule mod_mime.c>
            # (1)
            RemoveType gz

            # Serve correct content types
            AddType text/css              css.gz
            AddType text/javascript       js.gz

            # Serve correct content charset
            AddCharset utf-8 .css.gz \
                             .js.gz
		</IfModule>

        # Force proxies to cache gzipped and non-gzipped files separately
        Header append Vary Accept-Encoding
	</FilesMatch>

    # Serve correct encoding type
    AddEncoding gzip .gz
</IfModule>

HTACCESS;

	return apply_filters( 'rocket_htaccess_compressed_assets', $rules );
}

/**
 * Get list of CSS files to be excluded from async CSS.
 *
 * @since 3.6.2 deprecated
 * @since 2.10
 * @author Remy Perona
 *
 * @return array An array of URLs for the CSS files to be excluded.
 */
function get_rocket_exclude_async_css() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.6.2', '\WP_Rocket\Engine\CriticalPath\CriticalCSS::get_exclude_async_css()' );
	/**
	 * Filter list of async CSS files
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $exclude_async_css An array of URLs for the CSS files to be excluded.
	 */
	$exclude_async_css = (array) apply_filters( 'rocket_exclude_async_css', [] );
	$exclude_async_css = array_filter( $exclude_async_css );
	$exclude_async_css = array_flip( array_flip( $exclude_async_css ) );

	return $exclude_async_css;
}

/**
 * Changes the text on the Varnish one-click block.
 *
 * @deprecated 3.6.1
 * @since 3.0
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_wpengine_varnish_field( $settings ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\ThirdParty\Hostings\WPEngine::varnish_addon_title' );
	$settings['varnish_auto_purge']['title'] = sprintf(
	// Translators: %s = Hosting name.
		__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
		'WP Engine'
	);

	return $settings;
}

/**
 * Conflict with WP Engine caching system.
 *
 * @deprecated 3.6.1
 * @since 2.6.4
 *
 */
function rocket_stop_generate_caching_files_on_wpengine() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1' );
	add_filter( 'do_rocket_generate_caching_files', '__return_false' );
}

/**
 * Run WP Rocket preload bot after purged the Varnish cache via WP Engine Hosting.
 *
 * @deprecated 3.6.1
 * @since 2.6.4
 */
function rocket_run_rocket_bot_after_wpengine() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\ThirdParty\Hostings\WPEngine::run_rocket_bot_after_wpengine' );
	if ( wpe_param( 'purge-all' ) && defined( 'PWP_NAME' ) && check_admin_referer( PWP_NAME . '-config' ) ) {
		// Preload cache.
		run_rocket_bot();
		run_rocket_sitemap_preload();
	}
}

/**
 * Call the cache server to purge the cache with WP Engine hosting.
 *
 * @deprecated 3.6.1
 * @since 2.6.4
 */
function rocket_clean_wpengine() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\ThirdParty\Hostings\WPEngine::clean_wpengine' );
	if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
		WpeCommon::purge_memcached();
	}

	if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
		WpeCommon::purge_varnish_cache();
	}
}

/**
 * Gets WP Engine CDN Domain.
 *
 * @deprecated 3.6.1
 * @since 2.8.6
 *
 * return string $cdn_domain the WP Engine CDN Domain.
 */
function rocket_get_wp_engine_cdn_domain() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1' );
	global $wpe_netdna_domains, $wpe_netdna_domains_secure;

	$cdn_domain = '';
	$is_ssl     = '';

	if ( isset( $_SERVER['HTTPS'] ) ) {
		$is_ssl = sanitize_text_field( wp_unslash( $_SERVER['HTTPS'] ) );
	}

	if ( preg_match( '/^[oO][fF]{2}$/', $is_ssl ) ) {
		$is_ssl = false;  // have seen this!
	}

	$native_schema = $is_ssl ? 'https' : 'http';

	$domains = $wpe_netdna_domains;
	// Determine the CDN, if any.
	if ( $is_ssl ) {
		$domains = $wpe_netdna_domains_secure;
	}

	$wpengine   = WpeCommon::instance();
	$cdn_domain = $wpengine->get_cdn_domain( $domains, home_url(), $is_ssl );

	if ( ! empty( $cdn_domain ) ) {
		$cdn_domain = $native_schema . '://' . $cdn_domain;
	}

	return $cdn_domain;
}

/**
 * Add WP Rocket footprint on Buffer.
 *
 * @deprecated 3.6.1
 * @since 3.3.2
 *
 * @param string $buffer HTML content.
 *
 * @return string HTML with WP Rocket footprint.
 */
function rocket_wpengine_add_footprint( $buffer ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.1', '\WP_Rocket\ThirdParty\Hostings\WPEngine::add_footprint' );
	if ( ! preg_match( '/<\/html>/i', $buffer ) ) {
		return $buffer;
	}

	$footprint  = defined( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' )
		? "\n" . '<!-- Optimized for great performance'
		: "\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . WP_ROCKET_PLUGIN_NAME . '. Learn more: https://wp-rocket.me';
	$footprint .= ' -->';

	return $buffer . $footprint;
}

/**
 * Tell WP what to do when plugin is deactivated.
 *
 * @since 3.6.3 deprecated
 * @since 1.0
 */
function rocket_deactivation() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\Engine\Deactivation\Deactivation::deactivate_plugin' );
	global $is_apache;

	$filesystem = rocket_direct_filesystem();
	$wp_cache   = new WPCache( $filesystem );

	if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['rocket_nonce'] ), 'force_deactivation' ) ) {
		$causes = [];

		// .htaccess problem.
		if ( $is_apache && ! $filesystem->is_writable( get_home_path() . '.htaccess' ) ) {
			$causes[] = 'htaccess';
		}

		// wp-config problem.
		if (
			! $wp_cache->find_wpconfig_path()
			&&
			// This filter is documented in inc/Engine/Cache/WPCache.php.
			(bool) apply_filters( 'rocket_set_wp_cache_constant', true )
		) {
			$causes[] = 'wpconfig';
		}

		if ( count( $causes ) ) {
			set_transient( get_current_user_id() . '_donotdeactivaterocket', $causes );
			wp_safe_redirect( wp_get_referer() );
			die();
		}
	}

	// Delete config files.
	rocket_delete_config_file();

	if ( ! count( glob( WP_ROCKET_CONFIG_PATH . '*.php' ) ) ) {
		// Delete All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess( true );

		// Remove WP_CACHE constant in wp-config.php.
		$wp_cache->set_wp_cache_constant( false );

		// Delete content of advanced-cache.php.
		rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
	}

	// Update customer key & licence.
	wp_remote_get(
		WP_ROCKET_WEB_API . 'pause-licence.php',
		[
			'blocking' => false,
		]
	);

	// Delete transients.
	delete_transient( 'rocket_check_licence_30' );
	delete_transient( 'rocket_check_licence_1' );
	delete_site_transient( 'update_wprocket_response' );

	// Unschedule WP Cron events.
	wp_clear_scheduled_hook( 'rocket_facebook_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_google_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_cache_dir_size_check' );

	/**
	 * WP Rocket deactivation.
	 *
	 * @since  3.1.5
	 * @author Grégory Viguier
	 */
	do_action( 'rocket_deactivation' );

	( new Capabilities_Subscriber() )->remove_rocket_capabilities();
}

/**
 * Tell WP what to do when plugin is activated.
 *
 * @since 3.6.3
 * @since 1.1.0
 */
function rocket_activation() {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\Engine\Activation\Activation::deactivate_plugin' );
	( new Capabilities_Subscriber() )->add_rocket_capabilities();

	$filesystem = rocket_direct_filesystem();
	$wp_cache   = new WPCache( $filesystem );

	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	if ( defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
		require WP_ROCKET_INC_PATH . 'domain-mapping.php';
	}

	require WP_ROCKET_FUNCTIONS_PATH . 'options.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';

	if ( class_exists( 'WPaaS\Plugin' ) ) {
		require WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php';
	}
	if ( defined( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
		require WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php';
	}

	if ( rocket_valid_key() ) {
		// Add All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess();

		// Add WP_CACHE constant in wp-config.php.
		$wp_cache->set_wp_cache_constant( true );
	}

	// Create the cache folders (wp-rocket & min).
	rocket_init_cache_dir();

	// Create the config folder (wp-rocket-config).
	rocket_init_config_dir();

	// Create advanced-cache.php file.
	rocket_generate_advanced_cache_file( new AdvancedCache( WP_ROCKET_PATH . 'views/cache/', $filesystem ) );

	/**
	 * WP Rocket activation.
	 *
	 * @since  3.1.5
	 * @author Grégory Viguier
	 */
	do_action( 'rocket_activation' );

	// Update customer key & licence.
	wp_remote_get(
		WP_ROCKET_WEB_API . 'activate-licence.php',
		[
			'blocking' => false,
		]
	);

	wp_remote_get(
		home_url(),
		[
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'WP Rocket/Homepage Preload',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		]
	);
}

/**
 * Excludes Divi's Salvatorre script from JS minification
 *
 * Exclude it to prevent an error after minification/concatenation
 *
 * @since 3.6.3 deprecated
 * @since 2.9
 * @author Remy Perona
 *
 * @param Array $excluded_js An array of JS paths to be excluded.
 * @return Array the updated array of paths
 */
function rocket_exclude_js_divi( $excluded_js ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Themes\Divi::exclude_js' );
	if ( defined( 'ET_BUILDER_URI' ) ) {
		$excluded_js[] = str_replace( home_url(), '', ET_BUILDER_URI ) . '/scripts/salvattore.min.js';
	}

	return $excluded_js;
}

/**
 * Changes the text on the Varnish one-click block.
 *
 * @since 3.1
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_o2switch_varnish_field( $settings ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_addon_title' );
	// Translators: %s = Hosting name.
	$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'o2switch' );

	return $settings;
}

/**
 * Purge all the domain.
 *
 * @since 3.1
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 * @param string $url  The home url.
 */
function rocket_o2switch_varnish_clean_domain( $root, $lang, $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_clean_domain' );
	rocket_o2switch_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge a specific page.
 *
 * @since 3.1
 *
 * @param string $url The url to purge.
 */
function rocket_o2switch_varnish_clean_file( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_clean_file' );
	rocket_o2switch_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge the homepage and its pagination.
 *
 * @since 3.1
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 */
function rocket_o2switch_varnish_clean_home( $root, $lang ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_clean_home' );
	$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';

	rocket_o2switch_varnish_http_purge( $home_url );
	rocket_o2switch_varnish_http_purge( $home_pagination_url );
}

/**
 * Send data to Varnish.
 *
 * @since 3.1
 *
 * @param  string $url The URL to purge.
 */
function rocket_o2switch_varnish_http_purge( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::varnish_http_purge' );
	$parse_url = get_rocket_parse_url( $url );

	// This filter is documented in inc/functions/varnish.php.
	$headers = apply_filters(
		'rocket_varnish_purge_headers',
		[
			/**
			 * Filters the host value passed in the request headers
			 *
			 * @since 2.8.15
			 * @param string The host
			 */
			'host'           => apply_filters( 'rocket_varnish_purge_request_host', $parse_url['host'] ),
			'X-VC-Purge-Key' => O2SWITCH_VARNISH_PURGE_KEY,
		]
	);

	if ( 'vregex' === $parse_url['query'] ) {
		$headers['X-Purge-Regex'] = '.*';
	}

	/**
	 * Filter the Varnish IP to call
	 *
	 * @since 2.6.8
	 *
	 * @param string The Varnish IP
	 */
	$varnish_ip = apply_filters( 'rocket_varnish_ip', [] );

	if ( defined( 'WP_ROCKET_VARNISH_IP' ) && ! $varnish_ip ) {
		$varnish_ip = WP_ROCKET_VARNISH_IP;
	}

	/**
	 * Filter the HTTP protocol (scheme)
	 *
	 * @since 2.7.3
	 *
	 * @param string The HTTP protocol
	 */
	$scheme = apply_filters( 'rocket_varnish_http_purge_scheme', $parse_url['scheme'] );

	$parse_url['host'] = ( $varnish_ip ) ? $varnish_ip : $parse_url['host'];
	$purgeme           = $scheme . '://' . $parse_url['host'] . $parse_url['path'];

	wp_remote_request(
		$purgeme,
		[
			'method'      => 'PURGE',
			'blocking'    => false,
			'redirection' => 0,
			'headers'     => $headers,
		]
	);
}

/**
 * Remove expiration on HTML to prevent issue with Varnish cache.
 *
 * @since 3.1
 *
 * @param  string $rules htaccess rules.
 *
 * @return string        Updated htaccess rules.
 */
function rocket_o2switch_remove_html_expire( $rules ) {
	_deprecated_function( __FUNCTION__ . '()', '3.6.3', '\WP_Rocket\ThirdParty\Hostings\O2Switch::remove_html_expire' );
	$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
	$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

	return $rules;
}
