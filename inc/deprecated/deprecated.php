<?php
use MatthiasMullie\Minify;

defined( 'ABSPATH' ) || exit;

/**
 * Deprecated functions come here to die.
 */

if ( ! function_exists( 'rocket_replace_domain_mapping_siteurl' ) ) :
	/**
	 * Get Domain Mapping host based on original URL
	 *
	 * @since 2.2
	 * @deprecated 2.6.5
	 *
	 * @param string $url Original URL.
	 */
	function rocket_replace_domain_mapping_siteurl( $url = null ) {
		_deprecated_function( __FUNCTION__, '2.6.5' );
		return false;
	}
endif;

if ( ! function_exists( 'rocket_sanitize_cookie' ) ) :
	/**
	 * Used to sanitize values of the "Don't cache pages that use the following cookies" option.
	 *
	 * @since 2.6.4
	 * @deprecated 2.7
	 * @deprecated Use rocket_sanitize_key()
	 *
	 * @param string $cookie Cookie value to sanitize.
	 */
	function rocket_sanitize_cookie( $cookie ) {
		_deprecated_function( __FUNCTION__, '2.7', 'rocket_sanitize_key()' );
		return rocket_sanitize_key( $cookie );
	}
endif;

if ( ! function_exists( 'set_rocket_cloudflare_async' ) ) :
	/**
	 * Used to set the CloudFlare Rocket Loader value
	 *
	 * @since 2.5
	 * @deprecated 2.8.16
	 * @deprecated Use set_rocket_cloudflare_rocket_loader()
	 *
	 * @param string $cf_rocket_loader Value for the Rocket Loader.
	 */
	function set_rocket_cloudflare_async( $cf_rocket_loader ) {
		_deprecated_function( __FUNCTION__, '2.8.16', 'set_rocket_cloudflare_rocket_loader()' );
	}
endif;

if ( ! function_exists( 'set_rocket_cloudflare_cache_lvl' ) ) :
	/**
	 * Used to set the CloudFlare cache level
	 *
	 * @since 2.5
	 * @deprecated 2.8.16
	 * @deprecated Use set_rocket_cloudflare_cache_level()
	 *
	 * @param string $cf_cache_level Value for the cache level.
	 */
	function set_rocket_cloudflare_cache_lvl( $cf_cache_level ) {
		_deprecated_function( __FUNCTION__, '2.8.16', 'set_rocket_cloudflare_cache_level()' );
	}
endif;

if ( ! function_exists( 'rocket_delete_script_wp_version' ) ) :
	/**
	 * Used to remove version query string in CSS/JS URL
	 *
	 * @since 1.1.6
	 * @deprecated 2.9
	 * @deprecated Use rocket_browser_cache_busting()
	 *
	 * @param string $src Source URL for the JS/CSS.
	 */
	function rocket_delete_script_wp_version( $src ) {
		_deprecated_function( __FUNCTION__, '2.9', 'rocket_browser_cache_busting()' );
		return rocket_browser_cache_busting( $src );
	}
endif;

if ( ! function_exists( 'rocket_exclude_deferred_js' ) ) :
	/**
	 * Used to remove deferred JS files from the  buffer
	 *
	 * @since 1.1.0
	 * @deprecated 2.10
	 * @deprecated Use rocket_insert_deferred_js()
	 *
	 * @param string $buffer HTML code.
	 */
	function rocket_exclude_deferred_js( $buffer ) {
		_deprecated_function( __FUNCTION__, '2.10', 'rocket_insert_deferred_js()' );
		return rocket_insert_deferred_js( $buffer );
	}
endif;

if ( ! function_exists( 'is_rocket_cache_feed' ) ) :
	/**
	 * Check if we need to cache the feeds of the website
	 *
	 * @since 2.7
	 * @deprecated 2.10
	 *
	 * @return bool True if option is activated
	 */
	function is_rocket_cache_feed() {
		_deprecated_function( __FUNCTION__, '2.10' );
		return get_rocket_option( 'cache_feed', false );
	}
endif;

if ( ! function_exists( 'rocket_exclude_js_buddypress' ) ) :
	/**
	 * Excludes BuddyPress's plupload from JS minification
	 *
	 * Exclude it to prevent an error after minification/concatenation
	 * preventing the image upload from working correctly
	 *
	 * @since 2.8.10
	 * @deprecated 2.10.7
	 * @author Remy Perona
	 *
	 * @param Array $excluded_handle An array of JS handles enqueued in WordPress.
	 * @return Array the updated array of handles
	 */
	function rocket_exclude_js_buddypress( $excluded_handle ) {
		_deprecated_function( __FUNCTION__, '2.10.7' );
		return $excluded_handle;
	}
endif;

if ( ! function_exists( 'get_rocket_logins_exclude_pages' ) ) :
	/**
	 * Get hide login pages to automatically exclude them to the cache.
	 *
	 * @since 2.6
	 * @deprecated 2.11
	 *
	 * @return array $urls
	 */
	function get_rocket_logins_exclude_pages() {
		_deprecated_function( __FUNCTION__, '2.11' );
		return array();
	}
endif;

if ( ! function_exists( 'get_rocket_ecommerce_exclude_pages' ) ) :
	/**
	 * Get cart & checkout path with their translations to automatically exclude them to the cache.
	 *
	 * @since 2.4
	 * @deprecated 2.11
	 *
	 * @return array $urls
	 */
	function get_rocket_ecommerce_exclude_pages() {
		_deprecated_function( __FUNCTION__, '2.11' );
		return array();
	}
endif;


/**
 * Get list of JS files to deferred.
 *
 * @since 2.6
 * @deprecated 2.11
 *
 * @return array List of JS files.
 */
function get_rocket_deferred_js_files() {
	_deprecated_function( __FUNCTION__, '2.11' );
	/**
	 * Filter list of Deferred JavaScript files
	 *
	 * @since 1.1.0
	 *
	 * @param array List of Deferred JavaScript files
	 */
	$deferred_js_files = apply_filters( 'rocket_minify_deferred_js', get_rocket_option( 'deferred_js_files', array() ) );

	return $deferred_js_files;
}

/**
 * Add defer attribute to script that should be deferred
 *
 * @since 2.10 Use defer attribute instead of labJS
 * @since 1.1.0
 * @deprecated 2.11
 *
 * @param string $buffer HTML content in the buffer.
 * @return string Updated HTML content
 */
function rocket_insert_deferred_js( $buffer ) {
	_deprecated_function( __FUNCTION__, '2.11', 'rocket_defer_js()' );
	if ( get_rocket_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	// Get all JS files with this regex.
	preg_match_all( '#<script.*src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*></script>#iU', $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$url = strtok( $tags_match[1][ $i ], '?' );

		$deferred_js_files = array_flip( get_rocket_deferred_js_files() );

		// Check if this file should be deferred.
		if ( isset( $deferred_js_files[ $url ] ) ) {
			$deferred_tag = str_replace( '></script>', ' defer></script>', $tag );
			$buffer       = str_replace( $tag, $deferred_tag, $buffer );
		}
	}

	return $buffer;
}

/**
 * Used to display the defered module on settings form
 *
 * @since 1.1.0
 * @deprecated 2.11
 */
function rocket_defered_module() {
	_deprecated_function( __FUNCTION__, '2.11' );
	?>
	<fieldset>
	<legend class="screen-reader-text"><span><?php _e( '<strong>JS</strong> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>

	<div id="rkt-drop-deferred" class="rkt-module rkt-module-drop">

		<?php
		$deferred_js_files = get_rocket_option( 'deferred_js_files' );

		if ( $deferred_js_files ) {

			foreach ( $deferred_js_files as $k => $_url ) {
			?>

				<p class="rkt-module-drag">
					<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />

					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
				</p>
				<!-- .rkt-module-drag -->

			<?php
			}
		} else {
			// If no files yet, use this template inside #rkt-drop-deferred.
			?>

			<p class="rkt-module-drag">
				<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />
			</p>
			<!-- .rkt-module-drag -->

				<?php } ?>

	</div>
	<!-- .rkt-drop-deferred -->


	<div class="rkt-module-model hide-if-js">

		<p class="rkt-module-drag">
			<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

			<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />

			<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
		</p>
		<!-- .rkt-module-drag -->
	</div>
	<!-- .rkt-model-deferred-->

	<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add URL', 'rocket' ); ?></a></p>

<?php
}

/**
 * Check if minify cache file exist and create it if not
 *
 * @since 2.10 Use wp_safe_remote_get() instead of curl
 * @since 2.1
 * @deprecated 2.11
 *
 * @param string $url        The minified URL with Minify Library.
 * @param string $pretty_url The minified URL cache file.
 * @return bool True if sucessfully saved the minify cache file, false otherwise
 */
function rocket_fetch_and_cache_minify( $url, $pretty_url ) {
	_deprecated_function( __FUNCTION__, '2.11', 'rocket_minify()' );

	return false;
}

/**
 * Minify a file and return the URL
 *
 * @since 2.10
 * @deprecated 2.11
 *
 * @param string $file File to minify.
 * @param bool   $force_pretty_url (default: true).
 * @param string $pretty_filename (default: null) The new filename if $force_pretty_url set to true.
 * @return string URL of the minified file
 */
function get_rocket_minify_file( $file, $force_pretty_url = true, $pretty_filename = null ) {
	_deprecated_function( __FUNCTION__, '2.11', 'get_rocket_minify_url()' );

	return $file;
}

/**
 * Get tag of a group of files or JS minified CSS
 *
 * @since 2.1
 * @deprecated 2.11
 *
 * @param array  $files List of files to minify (CSS or JS).
 * @param bool   $force_pretty_url (default: true).
 * @param string $pretty_filename (default: null) The new filename if $force_pretty_url set to true.
 * @return string $tags HTML tags for the minified CSS/JS files
 */
function get_rocket_minify_files( $files, $force_pretty_url = true, $pretty_filename = null ) {
	_deprecated_function( __FUNCTION__, '2.11', 'get_rocket_minify_url()' );

	return false;
}

/**
 * Used to minify and concat CSS files
 *
 * @since 1.1.0 Fix Bug with externals URLs like //ajax.google.com
 * @since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
 * @since 1.0
 * @deprecated 2.11
 *
 * @param string $buffer HTML content.
 * @return string Updated HTML content
 */
function rocket_minify_css( $buffer ) {
	_deprecated_function( __FUNCTION__, '2.11', 'rocket_minify_files()' );

	return rocket_minify_files( $buffer, 'css' );
}

/**
 * Used to minify and concat JavaScript files
 *
 * @since 1.1.0 Fix Bug with externals URLs like //ajax.google.com
 * @since 1.0.2 Remove the filter, remove the array_chunk, add an automatic way to cut strings to 255c max
 * @since 1.0
 * @deprecated 2.11
 *
 * @param string $buffer HTML content.
 * @return string Updated HTML content
 */
function rocket_minify_js( $buffer ) {
	_deprecated_function( __FUNCTION__, '2.11', 'rocket_minify_files()' );

	return rocket_minify_files( $buffer, 'js' );
}

/**
 * Minify CSS/JS files without concatenation.
 *
 * @since 2.10
 * @deprecated 2.11
 * @author Remy Perona
 *
 * @param string $buffer HTML code to parse.
 * @param string $extension css or js.
 * @return string Updated HTML code
 */
function rocket_minify_only( $buffer, $extension ) {
	_deprecated_function( __FUNCTION__, '2.11', 'rocket_minify_files()' );

	return rocket_minify_files( $buffer, $extension );
}

/**
 * Get all CSS files to exclude to the minification.
 *
 * @since 2.6
 * @deprecated 2.11
 *
 * @return array List of excluded CSS files.
 */
function get_rocket_exclude_css() {
	_deprecated_function( __FUNCTION__, '2.11', 'get_rocket_exclude_files()' );

	return get_rocket_exclude_files( 'css' );
}

/**
 * Get all JS files to exclude to the minification.
 *
 * @since 2.6
 * @deprecated 2.11
 *
 * @return array List of excluded JS files.
 */
function get_rocket_exclude_js() {
	_deprecated_function( __FUNCTION__, '2.11', 'get_rocket_exclude_files()' );

	return get_rocket_exclude_files( 'js' );
}

/**
 * Get all JS files to move in the footer during the minification.
 *
 * @since 2.6
 * @deprecated 2.11
 *
 * @return array List of JS files.
 */
function get_rocket_minify_js_in_footer() {
	_deprecated_function( __FUNCTION__, '2.11' );

	return array();
}

/**
 * Extract all enqueued CSS files which should be exclude to the minification
 *
 * @since 2.6
 * @deprecated 2.11
 */
function rocket_extract_excluded_css_files() {
	_deprecated_function( __FUNCTION__, '2.11' );

	return false;
}

/**
 * Extract all enqueued JS files which should be exclude to the minification
 *
 * @since 2.6.1
 * @deprecated 2.11
 */
function rocket_extract_excluded_js_files() {
	_deprecated_function( __FUNCTION__, '2.11' );

	return false;
}

/**
 * Extract all enqueued JS files which should be insert in the footer
 *
 * @since 2.10
 * @since 2.6
 * @deprecated 2.11
 */
function rocket_extract_js_files_from_footer() {
	_deprecated_function( __FUNCTION__, '2.11' );

	return false;
}

/**
 * Insert JS minify files in footer
 *
 * @since 2.2
 */
function rocket_insert_minify_js_in_footer() {
	_deprecated_function( __FUNCTION__, '2.11' );

	return false;
}

/**
 * Compatibility with WordPress multisite with subfolders websites
 *
 * @since 2.6.5
 * @deprecated 2.11
 *
 * @param string $url minified file URL.
 * @return string Updated minified file URL
 */
function rocket_fix_minify_multisite_path_issue( $url ) {
	_deprecated_function( __FUNCTION__, '2.11' );

	return $url;
}

/**
 * Force the minification to create only 1 file.
 *
 * @param int    $length maximum URL length.
 * @param string $ext file extension.
 * @return int Updated length
 */
function rocket_force_minify_combine_all( $length, $ext ) {
	_deprecated_function( __FUNCTION__, '2.11' );
	if ( 'css' === $ext && get_rocket_option( 'minify_css_combine_all', false ) ) {
		$length = PHP_INT_MAX;
	}

	if ( 'js' === $ext && get_rocket_option( 'minify_js_combine_all', false ) ) {
		$length = PHP_INT_MAX;
	}

	return $length;
}

/**
 * Add some CSS to display the dismiss cross
 *
 * @since 1.1.10
 * @deprecated 2.11
 */
function rocket_admin_print_styles() {
	_deprecated_function( __FUNCTION__, '2.11' );
}

/**
 * Optimizes the database depending on the option
 *
 * @since 2.8
 * @deprecated 2.11
 * @see Rocket_Background_Database_Optimisation->task()
 * @author Remy Perona
 *
 * @param string $type Type of optimization to perform.
 */
function rocket_database_optimize( $type ) {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Background_Database_Optimisation->task()' );
}

/**
 * Launches the database optimization from admin
 *
 * @since 2.8
 * @deprecated 2.11
 * @see Rocket_Database_Optimisation->optimize()
 * @author Remy Perona
 */
function rocket_optimize_database() {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Database_Optimisation->optimize()' );
}

/**
 * Count the number of items concerned by the database cleanup
 *
 * @since 2.8
 * @deprecated 2.11
 * @see Rocket_Database_Optimisation->count_cleanup_items()
 * @author Remy Perona
 *
 * @param string $type Item type to count.
 * @return int Number of items for this type
 */
function rocket_database_count_cleanup_items( $type ) {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Database_Optimisation->count_cleanup_items()' );

	return 0;
}

/**
 * Planning database optimization cron
 * If the task is not programmed, it is automatically triggered
 *
 * @since 2.8
 * @deprecated 2.11
 * @see Rocket_Database_Optimisation->database_optimization_scheduled()
 * @author Remy Perona
 */
function rocket_database_optimization_scheduled() {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Database_Optimisation->database_optimization_scheduled()' );
}

/**
 * Performs the database optimization
 *
 * @since 2.8
 * @deprecated 2.11
 * @see Rocket_Database_Optimisation->process_handler()
 * @author Remy Perona
 */
function do_rocket_database_optimization() {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Database_Optimisation->process_handler()' );
}

if ( ! function_exists( 'rocket_define_donotminify_constants' ) ) {
	/**
	 * Declare and set value to DONOTMINIFYCSS & DONOTMINIFYJS constant
	 *
	 * @since 2.6.2
	 * @deprecated 2.11
	 * @see rocket_define_donotoptimize_constant()
	 *
	 * @param bool $value true or false.
	 */
	function rocket_define_donotminify_constants( $value ) {
		_deprecated_function( __FUNCTION__, '2.11', 'rocket_define_donotoptimize_constant' );

		if ( ! defined( 'DONOTMINIFYCSS' ) ) {
			define( 'DONOTMINIFYCSS', (bool) $value );
		}
		if ( ! defined( 'DONOTMINIFYJS' ) ) {
			define( 'DONOTMINIFYJS', (bool) $value );
		}
	}
}

if ( ! function_exists( 'rocket_define_donotasync_css_constant' ) ) {
	/**
	 * Declare and set value to DONOTMASYNCCSS constant
	 *
	 * @since 2.10
	 * @deprecated 2.11
	 * @see rocket_define_donotoptimize_constant()
	 * @author Remy Perona
	 *
	 * @param bool $value true or false.
	 */
	function rocket_define_donotasync_css_constant( $value ) {
		_deprecated_function( __FUNCTION__, '2.11', 'rocket_define_donotoptimize_constant' );

		if ( ! defined( 'DONOTASYNCCSS' ) ) {
			define( 'DONOTASYNCCSS', (bool) $value );
		}
	}
}

/**
 * Defer loading of CSS files
 *
 * @since 2.10
 * @deprecated 2.11
 * @see Rocket_Critical_CSS->async_css()
 * @author Remy Perona
 *
 * @param string $buffer HTML code.
 * @return string Updated HTML code
 */
function rocket_async_css( $buffer ) {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Critical_CSS->async_css()' );

	return $buffer;
}

/**
 * Insert critical CSS in the <head>
 *
 * @since 2.10
 * @deprecated 2.11
 * @see Rocket_Critical_CSS->insert_critical_css()
 * @author Remy Perona
 */
function rocket_insert_critical_css() {
	_deprecated_function( __FUNCTION__, '2.11', 'Rocket_Critical_CSS->insert_critical_css()' );
}

/**
 * Insert loadCSS script in <head>
 *
 * @since 2.10
 * @deprecated 2.11
 * @author Remy Perona
 */
function rocket_insert_load_css() {
	_deprecated_function( __FUNCTION__, '2.11' );
}

if ( ! function_exists( 'rocket_lazyload_async_script' ) ) {
	/**
	 * Add tags to the lazyload script to async and prevent concatenation
	 *
	 * @since 2.11
	 * @deprecated 2.11.2
	 * @author Remy Perona
	 *
	 * @param string $tag HTML for the script.
	 * @param string $handle Handle for the script.
	 *
	 * @return string Updated HTML
	 */
	function rocket_lazyload_async_script( $tag, $handle ) {
		_deprecated_function( __FUNCTION__, '2.11.2' );

		return $tag;
	}
}

if ( ! function_exists( 'is_rocket_cdn_on_ssl' ) ) {
	/**
	 * Check if we need to disable CDN on SSL pages
	 *
	 * @since 2.5
	 * @deprecated 3.0
	 *
	 * @return bool True if option is activated
	 */
	function is_rocket_cdn_on_ssl() {
		_deprecated_function( __FUNCTION__, '3.0' );
		return is_ssl() && get_rocket_option( 'cdn_ssl', 0 ) ? false : true;
	}
}

if ( ! function_exists( 'is_rocket_cache_ssl' ) ) {
	/**
	 * Check if we need to cache SSL requests of the website (if available)
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 *
	 * @return bool True if option is activated
	 */
	function is_rocket_cache_ssl() {
		_deprecated_function( __FUNCTION__, '3.0' );
		return false;
	}
}

if ( ! function_exists( 'rocket_reset_white_label_values_action' ) ) {
	/**
	 * Reset White Label values to WP Rocket default values
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 */
	function rocket_reset_white_label_values_action() {
		_deprecated_function( __FUNCTION__, '3.0' );
		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'rocket_resetwl' ) ) {
			rocket_reset_white_label_values( true );
		}
		wp_safe_redirect( add_query_arg( 'page', 'wprocket', remove_query_arg( 'page', wp_get_referer() ) ) );
		die();
	}
}

if ( ! function_exists( 'rocket_white_label' ) ) {
	/**
	 * White Label the plugin, if you need to
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 *
	 * @param array $plugins An array of plugins installed.
	 * @return array Updated array of plugins installed
	 */
	function rocket_white_label( $plugins ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		$white_label_description = get_rocket_option( 'wl_description' );
		// We change the plugin's header.
		$plugins['wp-rocket/wp-rocket.php'] = array(
			'Name'        => get_rocket_option( 'wl_plugin_name' ),
			'PluginURI'   => get_rocket_option( 'wl_plugin_URI' ),
			'Version'     => isset( $plugins['wp-rocket/wp-rocket.php']['Version'] ) ? $plugins['wp-rocket/wp-rocket.php']['Version'] : '',
			'Description' => reset( ( $white_label_description ) ),
			'Author'      => get_rocket_option( 'wl_author' ),
			'AuthorURI'   => get_rocket_option( 'wl_author_URI' ),
			'TextDomain'  => isset( $plugins['wp-rocket/wp-rocket.php']['TextDomain'] ) ? $plugins['wp-rocket/wp-rocket.php']['TextDomain'] : '',
			'DomainPath'  => isset( $plugins['wp-rocket/wp-rocket.php']['DomainPath'] ) ? $plugins['wp-rocket/wp-rocket.php']['DomainPath'] : '',
		);

		// if white label, remove our names from contributors.
		if ( rocket_is_white_label() ) {
			remove_filter( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );
		}

		return $plugins;
	}
}

if ( ! function_exists( 'rocket_is_white_label' ) ) {
	/**
	 * Is this version White Labeled?
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 */
	function rocket_is_white_label() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$options = '';
		$names   = array(
			'wl_plugin_name',
			'wl_plugin_URI',
			'wl_description',
			'wl_author',
			'wl_author_URI',
		);

		foreach ( $names as $value ) {
			$option   = get_rocket_option( $value );
			$options .= ! is_array( $option ) ? $option : reset( ( $option ) );
		}

		return '7ddca92d3d48d4da715a90ebcb3ec1f0' !== md5( $options );
	}
}

if ( ! function_exists( 'rocket_reset_white_label_values' ) ) {
	/**
	 * Reset white label options
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 *
	 * @param bool $hack_post true if we need to modify the $_POST content, false otherwise.
	 * @return void
	 */
	function rocket_reset_white_label_values( $hack_post ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		// White Label default values - !!! DO NOT TRANSLATE !!!
		$options                   = get_option( WP_ROCKET_SLUG );
		$options['wl_plugin_name'] = 'WP Rocket';
		$options['wl_plugin_slug'] = 'wprocket';
		$options['wl_plugin_URI']  = 'https://wp-rocket.me';
		$options['wl_description'] = array( 'The best WordPress performance plugin.' );
		$options['wl_author']      = 'WP Media';
		$options['wl_author_URI']  = 'https://wp-media.me';

		if ( $hack_post ) {
			// hack $_POST to force refresh of files, sorry.
			$_POST['page'] = 'wprocket';
		}

		update_option( WP_ROCKET_SLUG, $options );
	}
}

if ( ! function_exists( 'rocket_check_no_empty_name' ) ) {
	/**
	 * When you're doing an update, the constant does not contain yet your option or any value, reset and redirect!
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 */
	function rocket_check_no_empty_name() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$wl_plugin_name = trim( get_rocket_option( 'wl_plugin_name' ) );

		if ( empty( $wl_plugin_name ) ) {
			wp_safe_redirect( $_SERVER['REQUEST_URI'] );
			die();
		}
	}
}

if ( ! function_exists( 'rocket_correct_capability_for_options_page' ) ) {
	/**
	 * Fix the capability for our capacity filter hook
	 *
	 * @since 2.6
	 * @deprecated 3.0
	 * @see WP_Rocket\Engine\Settings\Page->required_capability()
	 *
	 * @param string $capability Capacity to access WP Rocket options.
	 * @return string Updated capacity
	 */
	function rocket_correct_capability_for_options_page( $capability ) {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Engine\Settings\Page->required_compatibility()' );
		/** This filter is documented in inc/admin-bar.php */
		return apply_filters( 'rocket_capacity', 'manage_options' );
	}
}

if ( ! function_exists( 'rocket_admin_menu' ) ) {
	/**
	 * Add submenu in menu "Settings"
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 * @see WP_Rocket\Engine\Settings\Page->add_admin_page()
	 */
	function rocket_admin_menu() {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Engine\Settings\Page->add_admin_page()' );
		add_options_page( WP_ROCKET_PLUGIN_NAME, WP_ROCKET_PLUGIN_NAME, apply_filters( 'rocket_capacity', 'manage_options' ), WP_ROCKET_PLUGIN_SLUG, 'rocket_display_options' );
	}
}

if ( ! function_exists( 'rocket_include' ) ) {
	/**
	 * Used to include a file in any tab
	 *
	 * @since 2.2
	 * @deprecated 3.0
	 *
	 * @param array $args An array of arguments to include the file.
	 */
	function rocket_include( $args ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		include_once dirname( __FILE__ ) . '/' . str_replace( '..', '', $args['file'] ) . '.inc.php';
	}
}

if ( ! function_exists( 'rocket_register_setting' ) ) {
	/**
	 * Tell to WordPress to be confident with our setting, we are clean!
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 * @see WP_Rocket\Engine\Settings\Page->configure()
	 */
	function rocket_register_setting() {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Engine\Settings\Page->configure()' );
		register_setting( 'wp_rocket', WP_ROCKET_SLUG, 'rocket_settings_callback' );
	}
}

if ( ! function_exists( 'rocket_settings_callback' ) ) {
	/**
	 * Used to clean and sanitize the settings fields
	 *
	 * @since 1.0
	 *
	 * @param array $inputs An array of values submitted by the settings form.
	 */
	function rocket_settings_callback( $inputs ) {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Engine\Admin\Settings\Settings->sanitize_callback()' );
		if ( isset( $_GET['action'] ) && 'purge_cache' === $_GET['action'] ) {
			return $inputs;
		}

		/*
		* Option : Minification CSS & JS
		*/
		$inputs['minify_css'] = ! empty( $inputs['minify_css'] ) ? 1 : 0;
		$inputs['minify_js']  = ! empty( $inputs['minify_js'] ) ? 1 : 0;

		// Option: mobile cache.
		if ( rocket_is_mobile_plugin_active() ) {
			$inputs['cache_mobile'] = 1;
			$inputs['do_caching_mobile_files'] = 1;
		}

		if ( empty( $inputs['lazyload_iframes'] ) ) {
			$inputs['lazyload_youtube'] = 0;
		}

		/*
		* Option : Purge delay
		*/
		$inputs['purge_cron_interval'] = isset( $inputs['purge_cron_interval'] ) ? (int) $inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );
		$inputs['purge_cron_unit'] = isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );

		if ( $inputs['purge_cron_interval'] < 10 && 'MINUTE_IN_SECONDS' === $inputs['purge_cron_unit'] ) {
			$inputs['purge_cron_interval'] = 10;
		}

		/*
		* Option : Remove query strings
		*/
		$inputs['remove_query_strings'] = ! empty( $inputs['remove_query_strings'] ) ? 1 : 0;

		/*
		* Option : Prefetch DNS requests
		*/
		if ( ! empty( $inputs['dns_prefetch'] ) ) {
			if ( ! is_array( $inputs['dns_prefetch'] ) ) {
				$inputs['dns_prefetch'] = explode( "\n", $inputs['dns_prefetch'] );
			}
			$inputs['dns_prefetch'] = array_map( 'trim', $inputs['dns_prefetch'] );
			$inputs['dns_prefetch'] = array_map( 'esc_url', $inputs['dns_prefetch'] );
			$inputs['dns_prefetch'] = (array) array_filter( $inputs['dns_prefetch'] );
			$inputs['dns_prefetch'] = array_unique( $inputs['dns_prefetch'] );
		} else {
			$inputs['dns_prefetch'] = array();
		}

		/*
		* Option : Empty the cache of the following pages when updating an article
		*/
		if ( ! empty( $inputs['cache_purge_pages'] ) ) {
			if ( ! is_array( $inputs['cache_purge_pages'] ) ) {
				$inputs['cache_purge_pages'] = explode( "\n", $inputs['cache_purge_pages'] );
			}
			$inputs['cache_purge_pages'] = array_map( 'trim', $inputs['cache_purge_pages'] );
			$inputs['cache_purge_pages'] = array_map( 'esc_url', $inputs['cache_purge_pages'] );
			$inputs['cache_purge_pages'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_purge_pages'] );
			$inputs['cache_purge_pages'] = (array) array_filter( $inputs['cache_purge_pages'] );
			$inputs['cache_purge_pages'] = array_unique( $inputs['cache_purge_pages'] );
		} else {
			$inputs['cache_purge_pages'] = array();
		}

		/*
		* Option : Never cache the following pages
		*/
		if ( ! empty( $inputs['cache_reject_uri'] ) ) {
			if ( ! is_array( $inputs['cache_reject_uri'] ) ) {
				$inputs['cache_reject_uri'] = explode( "\n", $inputs['cache_reject_uri'] );
			}
			$inputs['cache_reject_uri'] = array_map( 'trim', $inputs['cache_reject_uri'] );
			$inputs['cache_reject_uri'] = array_map( 'esc_url', $inputs['cache_reject_uri'] );
			$inputs['cache_reject_uri'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_reject_uri'] );
			$inputs['cache_reject_uri'] = (array) array_filter( $inputs['cache_reject_uri'] );
			$inputs['cache_reject_uri'] = array_unique( $inputs['cache_reject_uri'] );
		} else {
			$inputs['cache_reject_uri'] = array();
		}

		/*
		* Option : Don't cache pages that use the following cookies
		*/
		if ( ! empty( $inputs['cache_reject_cookies'] ) ) {
			if ( ! is_array( $inputs['cache_reject_cookies'] ) ) {
				$inputs['cache_reject_cookies'] = explode( "\n", $inputs['cache_reject_cookies'] );
			}
			$inputs['cache_reject_cookies'] = array_map( 'trim', $inputs['cache_reject_cookies'] );
			$inputs['cache_reject_cookies'] = array_map( 'rocket_sanitize_key', $inputs['cache_reject_cookies'] );
			$inputs['cache_reject_cookies'] = (array) array_filter( $inputs['cache_reject_cookies'] );
			$inputs['cache_reject_cookies'] = array_unique( $inputs['cache_reject_cookies'] );
		} else {
			$inputs['cache_reject_cookies'] = array();
		}

		/*
		* Option : Cache pages that use the following query strings (GET parameters)
		*/
		if ( ! empty( $inputs['cache_query_strings'] ) ) {
			if ( ! is_array( $inputs['cache_query_strings'] ) ) {
				$inputs['cache_query_strings'] = explode( "\n", $inputs['cache_query_strings'] );
			}
			$inputs['cache_query_strings'] = array_map( 'trim', $inputs['cache_query_strings'] );
			$inputs['cache_query_strings'] = array_map( 'rocket_sanitize_key', $inputs['cache_query_strings'] );
			$inputs['cache_query_strings'] = (array) array_filter( $inputs['cache_query_strings'] );
			$inputs['cache_query_strings'] = array_unique( $inputs['cache_query_strings'] );
		} else {
			$inputs['cache_query_strings'] = array();
		}

		/*
		* Option : Never send cache pages for these user agents
		*/
		if ( ! empty( $inputs['cache_reject_ua'] ) ) {
			if ( ! is_array( $inputs['cache_reject_ua'] ) ) {
				$inputs['cache_reject_ua'] = explode( "\n", $inputs['cache_reject_ua'] );
			}
			$inputs['cache_reject_ua'] = array_map( 'trim', $inputs['cache_reject_ua'] );
			$inputs['cache_reject_ua'] = array_map( 'rocket_sanitize_ua', $inputs['cache_reject_ua'] );
			$inputs['cache_reject_ua'] = (array) array_filter( $inputs['cache_reject_ua'] );
			$inputs['cache_reject_ua'] = array_unique( $inputs['cache_reject_ua'] );
		} else {
			$inputs['cache_reject_ua'] = array();
		}

		/*
		* Option : CSS files to exclude of the minification
		*/
		if ( ! empty( $inputs['exclude_css'] ) ) {
			if ( ! is_array( $inputs['exclude_css'] ) ) {
				$inputs['exclude_css'] = explode( "\n", $inputs['exclude_css'] );
			}
			$inputs['exclude_css'] = array_map( 'trim', $inputs['exclude_css'] );
			$inputs['exclude_css'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_css'] );
			$inputs['exclude_css'] = array_map( 'rocket_sanitize_css', $inputs['exclude_css'] );
			$inputs['exclude_css'] = (array) array_filter( $inputs['exclude_css'] );
			$inputs['exclude_css'] = array_unique( $inputs['exclude_css'] );
		} else {
			$inputs['exclude_css'] = array();
		}

		/*
		* Option : JS files to exclude of the minification
		*/
		if ( ! empty( $inputs['exclude_js'] ) ) {
			if ( ! is_array( $inputs['exclude_js'] ) ) {
				$inputs['exclude_js'] = explode( "\n", $inputs['exclude_js'] );
			}
			$inputs['exclude_js'] = array_map( 'trim', $inputs['exclude_js'] );
			$inputs['exclude_js'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_js'] );
			$inputs['exclude_js'] = array_map( 'rocket_sanitize_js', $inputs['exclude_js'] );
			$inputs['exclude_js'] = (array) array_filter( $inputs['exclude_js'] );
			$inputs['exclude_js'] = array_unique( $inputs['exclude_js'] );
		} else {
			$inputs['exclude_js'] = array();
		}

		// Option: Async CSS.
		$inputs['async_css'] = ! empty( $inputs['async_css'] ) ? 1 : 0;

		// Option: Critical CSS.
		$inputs['critical_css'] = ! empty( $inputs['critical_css'] ) ? str_replace( array( '<style>', '</style>' ), '', wp_kses( $inputs['critical_css'], array( "\'", '\"' ) ) ) : '';

		/*
		* Option : JS files to exclude from defer JS
		*/
		if ( ! empty( $inputs['exclude_defer_js'] ) ) {
			if ( ! is_array( $inputs['exclude_defer_js'] ) ) {
				$inputs['exclude_defer_js'] = explode( "\n", $inputs['exclude_defer_js'] );
			}
			$inputs['exclude_defer_js'] = array_map( 'trim', $inputs['exclude_defer_js'] );
			$inputs['exclude_defer_js'] = array_unique( $inputs['exclude_defer_js'] );
			$inputs['exclude_defer_js'] = array_map( 'rocket_sanitize_js', $inputs['exclude_defer_js'] );
			$inputs['exclude_defer_js'] = array_filter( $inputs['exclude_defer_js'] );
		} else {
			$inputs['exclude_defer_js'] = array();
		}

		/**
		 * Database options
		 */
		$inputs['database_revisions']          = ! empty( $inputs['database_revisions'] ) ? 1 : 0;
		$inputs['database_auto_drafts']        = ! empty( $inputs['database_auto_drafts'] ) ? 1 : 0;
		$inputs['database_trashed_posts']      = ! empty( $inputs['database_trashed_posts'] ) ? 1 : 0;
		$inputs['database_spam_comments']      = ! empty( $inputs['database_spam_comments'] ) ? 1 : 0;
		$inputs['database_trashed_comments']   = ! empty( $inputs['database_trashed_comments'] ) ? 1 : 0;
		$inputs['database_expired_transients'] = ! empty( $inputs['database_expired_transients'] ) ? 1 : 0;
		$inputs['database_all_transients']     = ! empty( $inputs['database_all_transients'] ) ? 1 : 0;
		$inputs['database_optimize_tables']    = ! empty( $inputs['database_optimize_tables'] ) ? 1 : 0;
		$inputs['schedule_automatic_cleanup']  = ! empty( $inputs['schedule_automatic_cleanup'] ) ? 1 : 0;
		$inputs['automatic_cleanup_frequency'] = ! empty( $inputs['automatic_cleanup_frequency'] ) ? $inputs['automatic_cleanup_frequency'] : '';

		if ( 1 !== $inputs['schedule_automatic_cleanup'] && ( 'daily' !== $inputs['automatic_cleanup_frequency'] || 'weekly' !== $inputs['automatic_cleanup_frequency'] || 'monthly' !== $inputs['automatic_cleanup_frequency'] ) ) {
			unset( $inputs['automatic_cleanup_frequency'] );
		}

		/**
		 * Options: Activate bot preload
		 */
		$inputs['manual_preload']    = ! empty( $inputs['manual_preload'] ) ? 1 : 0;
		$inputs['automatic_preload'] = ! empty( $inputs['automatic_preload'] ) ? 1 : 0;

		/*
		* Option: activate sitemap preload
		*/
		$inputs['sitemap_preload'] = ! empty( $inputs['sitemap_preload'] ) ? 1 : 0;

		/*
		* Option : XML sitemaps URLs
		*/
		if ( ! empty( $inputs['sitemaps'] ) ) {
			if ( ! is_array( $inputs['sitemaps'] ) ) {
				$inputs['sitemaps'] = explode( "\n", $inputs['sitemaps'] );
			}
			$inputs['sitemaps'] = array_map( 'trim', $inputs['sitemaps'] );
			$inputs['sitemaps'] = array_map( 'rocket_sanitize_xml', $inputs['sitemaps'] );
			$inputs['sitemaps'] = (array) array_filter( $inputs['sitemaps'] );
			$inputs['sitemaps'] = array_unique( $inputs['sitemaps'] );
		} else {
			$inputs['sitemaps'] = array();
		}

		/*
		* Option : CloudFlare Domain
		*/
		if ( ! empty( $inputs['cloudflare_domain'] ) ) {
			$inputs['cloudflare_domain'] = rocket_get_domain( $inputs['cloudflare_domain'] );
		} else {
			$inputs['cloudflare_domain'] = '';
		}

		$inputs['cloudflare_devmode'] = ( isset( $inputs['cloudflare_devmode'] ) && is_numeric( $inputs['cloudflare_devmode'] ) ) ? (int) $inputs['cloudflare_devmode'] : 0;
		$inputs['cloudflare_auto_settings'] = ( isset( $inputs['cloudflare_auto_settings'] ) && is_numeric( $inputs['cloudflare_auto_settings'] ) ) ? (int) $inputs['cloudflare_auto_settings'] : 0;

		/*
		* Option : CloudFlare
		*/
		if ( defined( 'WP_ROCKET_CF_API_KEY' ) ) {
			$inputs['cloudflare_api_key'] = get_rocket_option( 'cloudflare_api_key' );
		}

		/*
		* Option : CDN
		*/
		$inputs['cdn_cnames'] = isset( $inputs['cdn_cnames'] ) ? array_unique( array_filter( $inputs['cdn_cnames'] ) ) : array();

		if ( ! $inputs['cdn_cnames'] ) {
			$inputs['cdn_zone'] = array();
		} else {
			$total_cdn_cnames = max( array_keys( $inputs['cdn_cnames'] ) );
			for ( $i = 0; $i <= $total_cdn_cnames; $i++ ) {
				if ( ! isset( $inputs['cdn_cnames'][ $i ] ) ) {
					unset( $inputs['cdn_zone'][ $i ] );
				} else {
					$inputs['cdn_zone'][ $i ] = isset( $inputs['cdn_zone'][ $i ] ) ? $inputs['cdn_zone'][ $i ] : 'all';
				}
			}

			$inputs['cdn_cnames']   = array_values( $inputs['cdn_cnames'] );
			$inputs['cdn_cnames']   = array_map( 'untrailingslashit', $inputs['cdn_cnames'] );
			ksort( $inputs['cdn_zone'] );
			$inputs['cdn_zone']     = array_values( $inputs['cdn_zone'] );
		}

		/*
		* Option : Files to exclude of the CDN process
		*/
		if ( ! empty( $inputs['cdn_reject_files'] ) ) {
			if ( ! is_array( $inputs['cdn_reject_files'] ) ) {
				$inputs['cdn_reject_files'] = explode( "\n", $inputs['cdn_reject_files'] );
			}
			$inputs['cdn_reject_files'] = array_map( 'trim', $inputs['cdn_reject_files'] );
			$inputs['cdn_reject_files'] = array_map( 'rocket_clean_exclude_file', $inputs['cdn_reject_files'] );
			$inputs['cdn_reject_files'] = (array) array_filter( $inputs['cdn_reject_files'] );
			$inputs['cdn_reject_files'] = array_unique( $inputs['cdn_reject_files'] );
		} else {
			$inputs['cdn_reject_files'] = array();
		}

		/*
		* Option: Support
		*/
		$fake_options = array(
			'support_summary',
			'support_description',
			'support_documentation_validation',
		);

		foreach ( $fake_options as $option ) {
			if ( isset( $inputs[ $option ] ) ) {
				unset( $inputs[ $option ] );
			}
		}

		if ( isset( $_FILES['import'] ) && 0 !== $_FILES['import']['size'] && $settings = rocket_handle_settings_import( $_FILES['import'], 'wp-rocket', $inputs ) ) {
			$inputs = $settings;
		}

		if ( ! rocket_valid_key() ) {
			$checked = rocket_check_key();
		}

		if ( isset( $checked ) && is_array( $checked ) ) {
			$inputs['consumer_key']   = $checked['consumer_key'];
			$inputs['consumer_email'] = $checked['consumer_email'];
			$inputs['secret_key']     = $checked['secret_key'];
		}

		if ( rocket_valid_key() && ! empty( $inputs['secret_key'] ) && ! isset( $inputs['ignore'] ) ) {
			unset( $inputs['ignore'] );
			add_settings_error( 'general', 'settings_updated', __( 'Settings saved.', 'rocket' ), 'updated' );
		}

		return apply_filters( 'rocket_inputs_sanitize', $inputs );
	}
}

if ( ! function_exists( 'rocket_import_upload_form' ) ) {
	/**
	 * Outputs the form used by the importers to accept the data to be imported
	 *
	 * @since 2.2
	 * @deprecated 3.0
	 *
	 * @see WP_Rocket\Admin\Render->render_import_form();
	 */
	function rocket_import_upload_form() {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Admin\Render->render_import_form()' );
		/**
		 * Filter the maximum allowed upload size for import files.
		 *
		 * @since (WordPress) 2.3.0
		 *
		 * @see wp_max_upload_size()
		 *
		 * @param int $max_upload_size Allowed upload size. Default 1 MB.
		 */
		$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // Filter from WP Core.
		$size       = size_format( $bytes );
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) {
			?>
			<div class="error"><p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'rocket' ); ?></p>
			<p><strong><?php echo $upload_dir['error']; ?></strong></p></div>
		<?php
		} else {
			?>
			<p>
			<input type="file" id="upload" name="import" size="25" />
			<br />
			<label for="upload">
			<?php
			// translators: %s is the maximum upload size set on the current server.
			echo apply_filters( 'rocket_help', sprintf( __( 'Choose a file from your computer (maximum size: %s)', 'rocket' ), $size ), 'upload', 'help' );
			?>
			</label>
			<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
			</p>
			<?php
			submit_button( __( 'Upload file and import settings', 'rocket' ), 'button', 'import' );
		}
	}
}

if ( ! function_exists( 'rocket_field' ) ) {
	/**
	 * Used to display fields on settings form
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 *
	 * @param array $args An array of arguments to populate the settings fields.
	 */
	function rocket_field( $args ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		if ( ! is_array( reset( $args ) ) ) {
			$args = array( $args );
		}

		$full = $args;

		foreach ( $full as $args ) {
			if ( ! is_array( $args ) ) {
				continue;
			}

			if ( isset( $args['display'] ) && ! $args['display'] ) {
				continue;
			}
			$args['label_for']  = isset( $args['label_for'] ) ? $args['label_for'] : '';
			$args['name']       = isset( $args['name'] ) ? $args['name'] : $args['label_for'];
			$parent             = isset( $args['parent'] ) ? 'data-parent="' . sanitize_html_class( $args['parent'] ) . '"' : null;
			$placeholder        = isset( $args['placeholder'] ) ? 'placeholder="' . $args['placeholder'] . '" ' : '';
			$class              = isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : sanitize_html_class( $args['name'] );
			$class              .= ( $parent ) ? ' has-parent' : null;
			$label              = isset( $args['label'] ) ? $args['label'] : '';
			$default            = isset( $args['default'] ) ? $args['default'] : '';
			$readonly           = isset( $args['readonly'] ) && $args['readonly'] ? ' readonly="readonly" disabled="disabled"' : '';
			$cols               = isset( $args['cols'] ) ? (int) $args['cols'] : 50;
			$rows               = isset( $args['rows'] ) ? (int) $args['rows'] : 5;

			if ( ! isset( $args['fieldset'] ) || 'start' === $args['fieldset'] ) {
				printf(
					'<fieldset class="fieldname-%1$s fieldtype-%2$s %3$s">',
					sanitize_html_class( $args['name'] ),
					sanitize_html_class( $args['type'] ),
					isset( $args['parent'] ) ? 'fieldparent-' . sanitize_html_class( $args['parent'] ) : ''
				);
			}

			switch ( $args['type'] ) {
				case 'number':
				case 'email':
				case 'text':
					$value = get_rocket_option( $args['name'] );
					if ( false === $value ) {
						$value = $default;
					}

					$value          = esc_attr( $value );
					$number_options = 'number' === $args['type'] ? ' min="0" class="small-text"' : '';
					$autocomplete   = in_array( $args['name'], array( 'consumer_key', 'consumer_email' ), true ) ? ' autocomplete="off"' : '';
					$disabled       = ( 'consumer_key' === $args['name'] && defined( 'WP_ROCKET_KEY' ) ) || ( 'consumer_email' === $args['name'] && defined( 'WP_ROCKET_EMAIL' ) ) ? ' disabled="disabled"' : $readonly;
					?>

						<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
						<label><input<?php echo $autocomplete . $disabled; ?> type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>

					<?php
					break;

				case 'cloudflare_api_key':
					$value = get_rocket_option( $args['name'] );

					if ( 'cloudflare_api_key' === $args['name'] && defined( 'WP_ROCKET_CF_API_KEY' ) ) {
						$value = WP_ROCKET_CF_API_KEY;
					}

						$value          = esc_attr( $value );
						$disabled       = ( 'cloudflare_api_key' === $args['name'] && defined( 'WP_ROCKET_CF_API_KEY' ) ) ? ' disabled="disabled"' : $readonly;
						$cf_valid_credentials = false;
					if ( function_exists( 'rocket_cloudflare_valid_auth' ) ) {
						$cf_valid_credentials = ( is_wp_error( rocket_cloudflare_valid_auth() ) ) ? false : true;
					}
						?>
							<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
							<label>
								<input<?php echo $disabled; ?> type="text" id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?>
								<?php if ( $cf_valid_credentials ) { ?>
									<span id="rocket-check-cloudflare-api-container" class="rocket-cloudflare-api-valid">
										<span class="dashicons dashicons-yes" aria-hidden="true"></span> <?php _e( 'Your Cloudflare credentials are valid.', 'rocket' ); ?>
									</span>
								<?php } elseif ( ! $cf_valid_credentials && $value ) { ?>
									<span id="rocket-check-cloudflare-api-container">
										<span class="dashicons dashicons-no" aria-hidden="true"></span> <?php _e( 'Your Cloudflare credentials are invalid!', 'rocket' ); ?>
										</span>
								<?php } ?>
							</label>

					<?php
					break;

				case 'textarea':
					$t_temp = get_rocket_option( $args['name'], '' );

					if ( is_array( $t_temp ) ) {
						$t_temp = implode( "\n", $t_temp );
					}

					$value = ! empty( $t_temp ) ? esc_textarea( $t_temp ) : '';

					if ( ! $value ) {
						$value = $default;
					}
					?>

						<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
						<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="<?php echo $cols; ?>" rows="<?php echo $rows; ?>" class="<?php echo $class; ?>"
														<?php
														echo $readonly;
														echo $placeholder;
														echo $parent;
	?>
	><?php echo esc_html( $value ); ?></textarea>
						</label>

					<?php
					break;

				case 'checkbox':
					if ( isset( $args['label_screen'] ) ) {
					?>
						<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<?php } ?>
						<label><input type="checkbox" id="<?php echo $args['name']; ?>" class="<?php echo $class; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1"<?php echo $readonly; ?> <?php checked( get_rocket_option( $args['name'], $default ), 1 ); ?> <?php echo $parent; ?>/> <?php echo $args['label']; ?>
						</label>

				<?php
					break;

				case 'select':
					?>

						<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
						<label> <select id="<?php echo $args['name']; ?>" class="<?php echo $class; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]"<?php echo $readonly; ?> <?php echo $parent; ?>>
								<?php foreach ( $args['options'] as $val => $title ) { ?>
									<option value="<?php echo $val; ?>" <?php selected( get_rocket_option( $args['name'] ), $val ); ?>><?php echo $title; ?></option>
								<?php } ?>
								</select>
						<?php echo $label; ?>
						</label>

				<?php
					break;

				case 'submit_optimize':
					?>

				<input type="submit" name="wp_rocket_settings[submit_optimize]" id="rocket_submit_optimize" class="button button-primary" value="<?php _e( 'Save and optimize', 'rocket' ); ?>"> <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_optimize_database' ), 'rocket_optimize_database' ); ?>" class="button button-secondary"><?php _e( 'Optimize', 'rocket' ); ?></a>
				<?php
					break;

				case 'repeater':
					$fields = new WP_Rocket_Repeater_Field( $args );
					$fields->render();

					break;

				case 'helper_description':
					$description = isset( $args['description'] ) ? sprintf( '<p class="description help %1$s" %2$s><span class="dashicons dashicons-info" aria-hidden="true"></span><strong class="screen-reader-text">%3$s</strong> %4$s</p>', $class, $parent, _x( 'Note:', 'screen-reader-text', 'rocket' ), $args['description'] ) : '';
					echo apply_filters( 'rocket_help', $description, $args['name'], 'description' );

					break;

				case 'helper_performance':
					$description = isset( $args['description'] ) ? sprintf( '<p class="description help tip--perf %1$s" %2$s><span class="dashicons dashicons-performance" aria-hidden="true"></span><strong class="screen-reader-text">%3$s</strong> <strong>%4$s</strong></p>', $class, $parent, _x( 'Performance tip:', 'screen-reader-text', 'rocket' ), $args['description'] ) : '';
					echo apply_filters( 'rocket_help', $description, $args['name'], 'description' );

					break;

				case 'helper_detection':
					$description = isset( $args['description'] ) ? sprintf( '<p class="description help tip--detect %1$s" %2$s><span class="dashicons dashicons-visibility" aria-hidden="true"></span><strong class="screen-reader-text">%3$s</strong> %4$s</p>', $class, $parent, _x( 'Third-party feature detected:', 'screen-reader-text', 'rocket' ), $args['description'] ) : '';
					echo apply_filters( 'rocket_help', $description, $args['name'], 'description' );

					break;

				case 'helper_help':
					$description = isset( $args['description'] ) ? sprintf( '<p class="description help tip--use %1$s" %2$s>%3$s</p>', $class, $parent, $args['description'] ) : '';
					echo apply_filters( 'rocket_help', $description, $args['name'], 'help' );

					break;

				case 'helper_warning':
					$description = isset( $args['description'] ) ? sprintf( '<p class="description warning file-error %1$s" %2$s><span class="dashicons dashicons-warning" aria-hidden="true"></span><strong class="screen-reader-text">%3$s</strong> %4$s</p>', $class, $parent, _x( 'Warning:', 'screen-reader-text', 'rocket' ), $args['description'] ) : '';
					echo apply_filters( 'rocket_help', $description, $args['name'], 'warning' );

					break;

				case 'helper_panel_description':
					$description = isset( $args['description'] ) ? sprintf( '<div class="rocket-panel-description"><p class="%1$s" %2$s>%3$s</p></div>', $class, $parent, $args['description'] ) : '';
					echo $description;

					break;

				case 'rocket_export_form':
					?>
					<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_export' ), 'rocket_export' ); ?>" id="export" class="button button-secondary rocketicon"><?php _ex( 'Download settings', 'button text', 'rocket' ); ?></a>
					<?php
					break;

				case 'rocket_import_upload_form':
					rocket_import_upload_form( 'rocket_importer' );

					break;
				default:
					'Type manquant ou incorrect'; // ne pas traduire.

			}

			if ( ! isset( $args['fieldset'] ) || 'end' === $args['fieldset'] ) {
				echo '</fieldset>';
			}
		}

	}
}

if ( ! function_exists( 'rocket_cnames_module' ) ) {
	/**
	 * Used to display the CNAMES module on settings form
	 *
	 * @since 2.1
	 */
	function rocket_cnames_module() {
		_deprecated_function( __FUNCTION__, '3.0' );
		?>
			<legend class="screen-reader-text"><span><?php _e( 'Replace site\'s hostname with:', 'rocket' ); ?></span></legend>

			<div id="rkt-cnames" class="rkt-module">

				<?php

				$cnames = get_rocket_option( 'cdn_cnames' );
				$cnames_zone = get_rocket_option( 'cdn_zone' );

				if ( $cnames ) {

					foreach ( $cnames as $k => $_url ) {
					?>

					<p>

						<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][<?php echo $k; ?>]" value="<?php echo esc_attr( $_url ); ?>" />

						<label>
							<?php _e( 'reserved for', 'rocket' ); ?>
							<select name="wp_rocket_settings[cdn_zone][<?php echo $k; ?>]">
								<option value="all" <?php selected( $cnames_zone[ $k ], 'all' ); ?>><?php _e( 'All files', 'rocket' ); ?></option>
								<?php
								/**
								 * Controls the inclusion of images option for the CDN dropdown
								 *
								 * @since 2.10.7
								 * @author Remy Perona
								 *
								 * @param bool $allow true to add the option, false otherwise.
								 */
								if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
								?>
								<option value="images" <?php selected( $cnames_zone[ $k ], 'images' ); ?>><?php _e( 'Images', 'rocket' ); ?></option>
								<?php endif; ?>
								<option value="css_and_js" <?php selected( $cnames_zone[ $k ], 'css_and_js' ); ?>>CSS & JavaScript</option>
								<option value="js" <?php selected( $cnames_zone[ $k ], 'js' ); ?>>JavaScript</option>
								<option value="css" <?php selected( $cnames_zone[ $k ], 'css' ); ?>>CSS</option>
							</select>
						</label>
						<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

					</p>

					<?php
					}
				} else {

					// If no files yet, use this template inside #rkt-cnames.
					?>

					<p>

						<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

						<label>
							<?php _e( 'reserved for', 'rocket' ); ?>
							<select name="wp_rocket_settings[cdn_zone][]">
								<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
								<?php
								// this filter is defined in inc/admin/options.php.
								if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
								?>
								<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
								<?php endif; ?>
								<option value="css_and_js">CSS & JavaScript</option>
								<option value="js">JavaScript</option>
								<option value="css">CSS</option>
							</select>
						</label>

					</p>

				<?php } ?>

			</div>
			<?php // Clone Template. ?>
			<div class="rkt-module-model hide-if-js">

				<p>

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][]">
							<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
							<?php
							// this filter is defined in inc/admin/options.php.
							if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
							?>
							<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
							<?php endif; ?>
							<option value="css_and_js">CSS & JavaScript</option>
							<option value="js">JavaScript</option>
							<option value="css">CSS</option>
						</select>
					</label>
					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

				</p>

			</div>

			<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add CNAME', 'rocket' ); ?></a></p>

		</fieldset>

	<?php
	}
}

if ( ! function_exists( 'rocket_button' ) ) {
	/**
	 * Used to display buttons on settings form, tools tab
	 *
	 * @since 1.1.0
	 * @deprecated 3.0
	 *
	 * @param array $args An array of arguments to populate the button attributes.
	 */
	function rocket_button( $args ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		$button       = $args['button'];
		$desc         = isset( $args['helper_description'] ) ? $args['helper_description'] : null;
		$help         = isset( $args['helper_help'] ) ? $args['helper_help'] : null;
		$warning      = isset( $args['helper_warning'] ) ? $args['helper_warning'] : null;
		$id           = isset( $button['button_id'] ) ? sanitize_html_class( $button['button_id'] ) : null;
		$class        = sanitize_html_class( strip_tags( $button['button_label'] ) );
		$button_style = isset( $button['style'] ) ? 'button-' . sanitize_html_class( $button['style'] ) : 'button-secondary';

		if ( ! empty( $help ) ) {
			$help = '<p class="description help ' . $class . '">' . $help['description'] . '</p>';
		}
		if ( ! empty( $desc ) ) {
			$desc = sprintf( '<p class="description help %1$s"><span class="dashicons dashicons-info" aria-hidden="true"></span><strong class="screen-reader-text">%2$s</strong> %3$s</p>', $class, _x( 'Note:', 'screen-reader-text', 'rocket' ), $desc['description'] );
		}
		if ( ! empty( $warning ) ) {
			$warning = sprintf(
				'<p class="description warning file-error %1$s"><span class="dashicons dashicons-warning" aria-hidden="true"></span><strong class="screen-reader-text">%2$s</strong> %3$s</p>',
				$class,
				_x( 'Warning:', 'screen-reader-text', 'rocket' ),
				$warning['description']
			);
		}
	?>
		<fieldset class="fieldname-<?php echo $class; ?> fieldtype-button">
			<?php
			if ( isset( $button['url'] ) ) {
				echo '<a href="' . esc_url( $button['url'] ) . '" id="' . $id . '" class="' . $button_style . ' rocketicon rocketicon-' . $class . '">' . wp_kses_post( $button['button_label'] ) . '</a>';
			} else {
				echo '<button id="' . $id . '" class="' . $button_style . ' rocketicon rocketicon-' . $class . '">' . wp_kses_post( $button['button_label'] ) . '</button>';
			}
			?>


			<?php echo apply_filters( 'rocket_help', $desc, sanitize_key( strip_tags( $button['button_label'] ) ), 'description' ); ?>
			<?php echo apply_filters( 'rocket_help', $help, sanitize_key( strip_tags( $button['button_label'] ) ), 'help' ); ?>
			<?php echo apply_filters( 'rocket_help', $warning, sanitize_key( strip_tags( $button['button_label'] ) ), 'warning' ); ?>

		</fieldset>
	<?php
	}

	/**
	 * Used to display videos buttons on settings form
	 *
	 * @since 2.2
	 *
	 * @param array $args An array of arguments to populate the video attributes.
	 */
	function rocket_video( $args ) {
		$desc = '<p class="description desc ' . sanitize_html_class( $args['name'] ) . '">' . $args['description'] . '</p>';
	?>
		<fieldset class="fieldname-<?php echo $args['name']; ?> fieldtype-button">
			<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary fancybox rocketicon rocketicon-video"><?php _e( 'Watch the video', 'rocket' ); ?></a>
			<?php echo apply_filters( 'rocket_help', $desc, $args['name'], 'description' ); ?>
		</fieldset>
	<?php
	}
}

if ( ! function_exists( 'rocket_display_options' ) ) {
	/**
	 * The main settings page construtor using the required functions from WP
	 *
	 * @since 1.1.0 Add tabs, tools tab and change options severity
	 * @since 1.0
	 * @deprecated 3.0
	 */
	function rocket_display_options() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$modules = array(
			'api-key',
			'basic',
			'advanced',
			'optimization',
			'database',
			'preload',
			'cloudflare',
			'cdn',
			'varnish',
			'tools',
			'support',
		);

		foreach ( $modules as $module ) {
			require WP_ROCKET_ADMIN_UI_MODULES_PATH . $module . '.php';
		}

		$heading_tag = version_compare( $GLOBALS['wp_version'], '4.3' ) >= 0 ? 'h1' : 'h2';
		?>

		<div class="wrap">

		<<?php echo $heading_tag; ?>><?php echo WP_ROCKET_PLUGIN_NAME; ?> <small><sup><?php echo WP_ROCKET_VERSION; ?></sup></small></<?php echo $heading_tag; ?>>
		<form action="options.php" id="rocket_options" method="post" enctype="multipart/form-data">
			<?php
			settings_fields( 'wp_rocket' );

			rocket_hidden_fields(
				array(
					'consumer_key',
					'consumer_email',
					'secret_key',
					'license',
					'secret_cache_key',
					'minify_css_key',
					'minify_js_key',
					'version',
					'cloudflare_old_settings',
					'cloudflare_zone_id',
					'sitemap_preload_url_crawl',
				)
			);

			submit_button();
			?>
			<h2 class="nav-tab-wrapper hide-if-no-js">
				<?php if ( rocket_valid_key() ) { ?>
					<a href="#tab_basic" class="nav-tab"><?php _e( 'Basic', 'rocket' ); ?></a>
					<a href="#tab_optimization" class="nav-tab"><?php _e( 'Static Files', 'rocket' ); ?></a>
					<a href="#tab_cdn" class="nav-tab"><?php _e( 'CDN', 'rocket' ); ?></a>
					<a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced', 'rocket' ); ?></a>
					<a href="#tab_database" class="nav-tab"><?php _e( 'Database', 'rocket' ); ?></a>
					<a href="#tab_preload" class="nav-tab"><?php _e( 'Preload', 'rocket' ); ?></a>
					<?php if ( get_rocket_option( 'do_cloudflare' ) ) { ?>
						<a href="#tab_cloudflare" class="nav-tab">Cloudflare</a>
					<?php
					}
					/** This filter is documented in inc/admin/ui/modules/vanrish.php */
					if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) {
					?>
					<a href="#tab_varnish" class="nav-tab">Varnish</a>
					<?php } ?>
					<a href="#tab_tools" class="nav-tab"><?php _e( 'Tools', 'rocket' ); ?></a>
					<a href="#tab_support" class="nav-tab"><?php _e( 'Support', 'rocket' ); ?></a>
				<?php } else { ?>
					<a href="#tab_apikey" class="nav-tab"><?php _e( 'License', 'rocket' ); ?></a>
				<?php } ?>
				<?php
				do_action( 'rocket_tab', rocket_valid_key() );
				?>
			</h2>
			<div id="rockettabs">
				<?php if ( rocket_valid_key() ) { ?>
					<div class="rkt-tab" id="tab_basic"><?php do_settings_sections( 'rocket_basic' ); ?></div>
					<div class="rkt-tab" id="tab_optimization"><?php do_settings_sections( 'rocket_optimization' ); ?></div>
					<div class="rkt-tab" id="tab_cdn"><?php do_settings_sections( 'rocket_cdn' ); ?></div>
					<div class="rkt-tab" id="tab_advanced"><?php do_settings_sections( 'rocket_advanced' ); ?></div>
					<div class="rkt-tab" id="tab_database"><?php do_settings_sections( 'rocket_database' ); ?></div>
					<div class="rkt-tab" id="tab_preload"><?php do_settings_sections( 'rocket_preload' ); ?></div>
					<div class="rkt-tab" id="tab_cloudflare" <?php echo get_rocket_option( 'do_cloudflare' ) ? '' : 'style="display:none"'; ?>><?php do_settings_sections( 'rocket_cloudflare' ); ?></div>
					<?php
					/** This filter is documented in inc/admin/ui/modules/vanrish.php */
					if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) {
					?>
						<div class="rkt-tab" id="tab_varnish"><?php do_settings_sections( 'rocket_varnish' ); ?></div>
					<?php } ?>
					<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'rocket_tools' ); ?></div>
					<div class="rkt-tab rkt-tab-txt" id="tab_support"><?php do_settings_sections( 'rocket_support' ); ?></div>
				<?php } else { ?>
					<div class="rkt-tab" id="tab_apikey"><?php do_settings_sections( 'rocket_apikey' ); ?></div>
				<?php } ?>
				<?php
				do_action( 'rocket_tab_content', rocket_valid_key() );
				?>
			</div>
			<?php submit_button(); ?>
		</form>
	<?php
	}
}

if ( ! function_exists( 'rocket_hidden_fields' ) ) {
	/**
	 * Function used to print all hidden fields from rocket to avoid the loss of these.
	 *
	 * @since 2.1
	 * @deprecated 3.0
	 *
	 * @param array $fields An array of fields to add to WP Rocket settings.
	 */
	function rocket_hidden_fields( $fields ) {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket\Admin\Render->render_hidden_fields()' );
		if ( ! is_array( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {
			echo '<input type="hidden" name="wp_rocket_settings[' . $field . ']" value="' . esc_attr( get_rocket_option( $field ) ) . '" />';
		}
	}
}

if ( ! function_exists( 'wp_ajax_rocket_new_ticket_support' ) ) {
	/**
	 * Open a ticket support.
	 *
	 * @since 2.6
	 * @deprecated 3.0
	 */
	function wp_ajax_rocket_new_ticket_support() {
		_deprecated_function( __FUNCTION__, '3.0' );
		// rocket_capability is a typo (should have been rocket_capacity).
		if ( ! isset( $_POST['_wpnonce'], $_POST['summary'], $_POST['description'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wp_rocket-options' ) ||
			! current_user_can( apply_filters_deprecated( 'rocket_capability', array( 'manage_options' ), '2.8.9', 'rocket_capacity' ) ) ||
			! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
			) {
			return;
		}

		$response = wp_remote_post(
			WP_ROCKET_WEB_API . 'support/new-ticket.php',
			array(
				'timeout' => 10,
				'body'    => array(
					'data' => array(
						'user_email'           => defined( 'WP_ROCKET_EMAIL' ) ? sanitize_email( WP_ROCKET_EMAIL ) : '',
						'user_key'             => defined( 'WP_ROCKET_KEY' ) ? sanitize_key( WP_ROCKET_KEY ) : '',
						'user_website'         => home_url(),
						'wp_version'           => $GLOBALS['wp_version'],
						'wp_active_plugins'    => rocket_get_active_plugins(),
						'wp_rocket_version'    => WP_ROCKET_VERSION,
						'wp_rocket_options'    => get_option( WP_ROCKET_SLUG ),
						'support_summary'      => $_POST['summary'],
						'support_description'  => $_POST['description'],
					),
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			wp_send_json( wp_remote_retrieve_body( $response ) );
		} else {
			wp_send_json(
				array(
					'msg' => 'BAD_SERVER',
				)
			);
		}
	}
}

if ( ! function_exists( 'wp_ajax_rocket_helpscout_live_search' ) ) {
	/**
	 * Documentation suggestions based on the summary input from the new ticket support form.
	 *
	 * @since 2.6
	 * @deprecated 3.0
	 */
	function wp_ajax_rocket_helpscout_live_search() {
		_deprecated_function( __FUNCTION__, '3.0' );
		if ( current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			$query = filter_input( INPUT_POST, 'query' );
			$response = wp_remote_post(
				WP_ROCKET_WEB_MAIN . 'tools/wp-rocket/helpscout/livesearch.php',
				array(
					'timeout'   => 10,
					'body'      => array(
						'query' => esc_html( wp_strip_all_tags( $query, true ) ),
						'lang'  => get_locale(),
					),
				)
			);

			if ( ! is_wp_error( $response ) ) {
				wp_send_json( wp_remote_retrieve_body( $response ) );
			}
		}
	}
}

if ( ! function_exists( 'rocket_php_warning' ) ) {
	/**
	 * Warns if PHP version is less than 5.3 and offers to rollback.
	 *
	 * @since 2.11
	 * @deprecated 3.0
	 * @see WP_Rocket_Requirements_check::notice();
	 * @author Remy Perona
	 */
	function rocket_php_warning() {
		_deprecated_function( __FUNCTION__, '3.0', 'WP_Rocket_Requirements_check::notice()' );

		if ( version_compare( PHP_VERSION, '5.3' ) >= 0 ) {
			return;
		}
		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}
		// Translators: %1$s = Plugin name, %2$s = Plugin version, %3$s = PHP version required.
		echo '<div class="notice notice-error"><p>' . sprintf( __( '%1$s %2$s requires at least PHP %3$s to function properly. To use this version, please ask your web host how to upgrade your server to PHP %3$s or higher. If you are not able to upgrade, you can rollback to the previous version by using the button below.', 'rocket' ), WP_ROCKET_PLUGIN_NAME, WP_ROCKET_VERSION, '5.3' ) . '</p>
		<p><a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=rocket_rollback' ), 'rocket_rollback' ) . '" class="button">' .
		// Translators: %s = Previous plugin version.
		sprintf( __( 'Re-install version %s', 'rocket' ), WP_ROCKET_LASTVERSION )
		. '</a></p></div>';
	}
}

if ( ! function_exists( 'rocket_get_home_path' ) ) {
	/**
	 * Get the absolute filesystem path to the root of the WordPress installation.
	 *
	 * @since 2.11.7 copy function get_home_path() from WP core.
	 * @since 2.11.5
	 * @deprecated 3.0
	 *
	 * @author Chris Williams
	 *
	 * @return string Full filesystem path to the root of the WordPress installation.
	 */
	function rocket_get_home_path() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$home      = set_url_scheme( get_option( 'home' ), 'http' );
		$siteurl   = set_url_scheme( get_option( 'siteurl' ), 'http' );
		$home_path = ABSPATH;

		if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
			$wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
			$pos                 = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
			$home_path           = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
			$home_path           = trailingslashit( $home_path );
		}

		return str_replace( '\\', '/', $home_path );
	}
}

if ( ! function_exists( 'rocket_clean_cache_after_woocommerce_save_product_variation' ) ) {
	/**
	 * Clean product cache on variation update
	 *
	 * @since 2.9
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::clean_cache_after_woocommerce_save_product_variation()
	 * @author Remy Perona
	 *
	 * @param int $variation_id ID of the variation.
	 */
	function rocket_clean_cache_after_woocommerce_save_product_variation( $variation_id ) {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::clean_cache_after_woocommerce_save_product_variation()' );
		$product_id = wp_get_post_parent_id( $variation_id );
		if ( $product_id ) {
			rocket_clean_post( $product_id );
		}
	}
}

if ( ! function_exists( 'rocket_cache_v_query_string' ) ) {
	/**
	 * Automatically cache v query string when WC geolocation with cache compatibility option is active
	 *
	 * @since 2.8.6
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::cache_geolocation_query_string()
	 * @author Rémy Perona
	 *
	 * @param array $query_strings list of query strings to cache.
	 * @return array Updated list of query strings to cache
	 */
	function rocket_cache_v_query_string( $query_strings ) {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::cache_geolocation_query_string()' );
		if ( 'geolocation_ajax' === get_option( 'woocommerce_default_customer_address' ) ) {
			$query_strings[] = 'v';
		}

		return $query_strings;
	}
}

if ( ! function_exists( 'rocket_exclude_woocommerce_pages' ) ) {
	/**
	 * Exclude WooCommerce cart, checkout and account pages from caching
	 *
	 * @since 2.11 Moved to 3rd party
	 * @since 2.4
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::exclude_pages()
	 *
	 * @param array $urls An array of excluded pages.
	 * @return array Updated array of excluded pages
	 */
	function rocket_exclude_woocommerce_pages( $urls ) {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::exclude_pages()' );
		if ( function_exists( 'WC' ) && function_exists( 'wc_get_page_id' ) ) {
			if ( wc_get_page_id( 'checkout' ) && wc_get_page_id( 'checkout' ) !== -1 && wc_get_page_id( 'checkout' ) !== (int) get_option( 'page_on_front' ) ) {
				$checkout_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'checkout' ), 'page', '(.*)' );
				$urls = array_merge( $urls, $checkout_urls );
			}

			if ( wc_get_page_id( 'cart' ) && wc_get_page_id( 'cart' ) !== -1 && wc_get_page_id( 'cart' ) !== (int) get_option( 'page_on_front' ) ) {
				$cart_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'cart' ) );
				$urls = array_merge( $urls, $cart_urls );
			}

			if ( wc_get_page_id( 'myaccount' ) && wc_get_page_id( 'myaccount' ) !== -1 && wc_get_page_id( 'myaccount' ) !== (int) get_option( 'page_on_front' ) ) {
				$cart_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'myaccount' ), 'page', '(.*)' );
				$urls = array_merge( $urls, $cart_urls );
			}
		}

		return $urls;
	}
}

if ( ! function_exists( 'rocket_activate_woocommerce' ) ) {
	/**
	 * Add query string to exclusion when activating the plugin
	 *
	 * @since 2.8.6
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::activate_woocommerce()
	 * @author Rémy Perona
	 */
	function rocket_activate_woocommerce() {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::activate_woocommerce()' );
		add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_woocommerce_pages' );
		add_filter( 'rocket_cache_query_strings', 'rocket_cache_v_query_string' );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}
}

if ( ! function_exists( 'rocket_deactivate_woocommerce' ) ) {
	/**
	 * Remove query string from exclusion when deactivating the plugin
	 *
	 * @since 2.8.6
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::deactivate_woocommerce()
	 * @author Rémy Perona
	 */
	function rocket_deactivate_woocommerce() {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::deactivate_woocommerce()' );
		remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_woocommerce_pages' );
		remove_filter( 'rocket_cache_query_strings', 'rocket_cache_v_query_string' );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}
}

if ( ! function_exists( 'rocket_exclude_wc_rest_api' ) ) {
	/**
	 * Exclude WooCommerce REST API URL from cache
	 *
	 * @since 2.6.5
	 * @deprecated 3.1
	 * @see WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::exclude_wc_rest_api()
	 *
	 * @param array $uri URLs to exclude from cache.
	 * @return array Updated list of URLs to exclude from cache
	 */
	function rocket_exclude_wc_rest_api( $uri ) {
		_deprecated_function( __FUNCTION__, '3.1', 'WP_Rocket\Third_Party\Plugins\Ecommerce\WooCommerce::exclude_wc_rest_api()' );
		/**
		  * By default, don't cache the WooCommerce REST API.
		  *
		  * @since 2.6.5
		  *
		  * @param bool false will force to cache the WooCommerce REST API
		 */
		$rocket_cache_reject_wc_rest_api = apply_filters( 'rocket_cache_reject_wc_rest_api', true );

		// Exclude WooCommerce REST API.
		if ( $rocket_cache_reject_wc_rest_api ) {
			$uri[] = rocket_clean_exclude_file( home_url( '/wc-api/v(.*)' ) );
		}

		return $uri;
	}
}

if ( ! function_exists( 'rocket_minify_process' ) ) {
	/**
	 * Launch WP Rocket minification process (HTML, CSS and JavaScript)
	 *
	 * @since 2.10  New process for minification without concatenation
	 * @since 1.3.0 This process is called via the new filter rocket_buffer
	 * @since 1.1.6 Minify inline CSS and JavaScript
	 * @since 1.0
	 * @deprecated 3.1
	 *
	 * @param string $buffer HTML content.
	 * @return string Modified HTML content
	 */
	function rocket_minify_process( $buffer ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		$enable_css          = get_rocket_option( 'minify_css' );
		$enable_js           = get_rocket_option( 'minify_js' );
		$enable_google_fonts = get_rocket_option( 'minify_google_fonts' );

		if ( $enable_css || $enable_js || $enable_google_fonts ) {
			list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );
		}

		// Minify JavaScript.
		if ( $enable_js && ( ! defined( 'DONOTROCKETOPTIMIZE' ) || ! DONOTROCKETOPTIMIZE ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
			$buffer = rocket_minify_files( $buffer, 'js' );
		}

		// Minify CSS.
		if ( $enable_css && ( ! defined( 'DONOTROCKETOPTIMIZE' ) || ! DONOTROCKETOPTIMIZE ) && ( ! defined( 'DONOTMINIFYCSS' ) || ! DONOTMINIFYCSS ) && ! is_rocket_post_excluded_option( 'minify_css' ) ) {
			$buffer = rocket_minify_files( $buffer, 'css' );
		}

		// Concatenate Google Fonts.
		if ( $enable_google_fonts ) {
			$buffer = rocket_concatenate_google_fonts( $buffer );
		}

		if ( $enable_css || $enable_js || $enable_google_fonts ) {
			$buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );
		}

		return $buffer;
	}
}

if ( ! function_exists( 'rocket_minify_html' ) ) {
	/**
	 * Minifies inline HTML
	 *
	 * @since 2.10 Do the HTML minification independently and hook it later to prevent conflicts
	 * @since 1.1.12
	 * @deprecated 3.1
	 *
	 * @param string $buffer HTML content.
	 * @return string Updated HTML content
	 */
	function rocket_minify_html( $buffer ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( ! get_rocket_option( 'minify_html' ) || is_rocket_post_excluded_option( 'minify_html' ) ) {
			return $buffer;
		}

		$html_options = array(
			'cssMinifier' => 'rocket_minify_inline_css',
		);

		/**
		 * Filter options of minify inline HTML
		 *
		 * @since 1.1.12
		 *
		 * @param array $html_options Options of minify inline HTML.
		 */
		$html_options = apply_filters( 'rocket_minify_html_options', $html_options );

		return Minify_HTML::minify( $buffer, $html_options );
	}
}

if ( ! function_exists( 'rocket_fix_ssl_minify' ) ) {
	/**
	 * Fix issue with SSL and minification
	 *
	 * @since 2.3
	 * @deprecated 3.1
	 *
	 * @param string $url An url to filter to set the scheme to https if needed.
	 * @return string Updated URL
	 */
	function rocket_fix_ssl_minify( $url ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( is_ssl() && false === strpos( $url, 'https://' ) && ! in_array( rocket_extract_url_component( $url, PHP_URL_HOST ), get_rocket_cnames_host( array( 'all', 'css_js', 'css', 'js' ) ), true ) ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'rocket_minify_i18n_multidomain' ) ) {
	/**
	 * Compatibility with multilingual plugins & multidomain configuration
	 *
	 * @since 2.6.13 Regression Fix: Apply CDN on minified CSS and JS files by checking the CNAME host
	 * @since 2.6.8
	 * @deprecated 3.1
	 *
	 * @param string $url Minified file URL.
	 * @return string Updated minified file URL
	 */
	function rocket_minify_i18n_multidomain( $url ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( ! rocket_has_i18n() ) {
			return $url;
		}

		$url_host = rocket_extract_url_component( $url, PHP_URL_HOST );
		$zone     = array( 'all', 'css_and_js' );
		$current_filter = current_filter();

		// Add only CSS zone.
		if ( 'rocket_css_url' === $current_filter ) {
			$zone[] = 'css';
		}

		// Add only JS zone.
		if ( 'rocket_js_url' === $current_filter ) {
			$zone[] = 'js';
		}

		$cnames = get_rocket_cdn_cnames( $zone );
		$cnames = array_map( 'rocket_remove_url_protocol' , $cnames );

		if ( $url_host !== $_SERVER['HTTP_HOST'] && in_array( $_SERVER['HTTP_HOST'], get_rocket_i18n_host(), true ) && ! in_array( $url_host, $cnames, true ) ) {
			$url = str_replace( $url_host, $_SERVER['HTTP_HOST'], $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'rocket_get_js_enqueued_in_head' ) ) {
	/**
	 * Get all src for JS files already enqueued in head
	 *
	 * @since 2.10
	 * @deprecated 3.1
	 * @author Remy Perona
	 */
	function rocket_get_js_enqueued_in_head() {
		_deprecated_function( __FUNCTION__, '3.1' );

		global $wp_scripts, $rocket_js_enqueued_in_head;

		if ( ! (bool) $wp_scripts->done ) {
			return;
		}

		foreach ( $wp_scripts->done as $handle ) {
			if ( ! empty( $wp_scripts->registered[ $handle ]->src ) ) {
				$rocket_js_enqueued_in_head[] = str_replace( '#', '\#', rocket_clean_exclude_file( $wp_scripts->registered[ $handle ]->src ) );
			}
		}
	}
}

if ( ! function_exists( 'get_rocket_exclude_files' ) ) {
	/**
	 * Get all files to exclude from minification/concatenation.
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $extension Type of files to exclude.
	 * @return array Array of excluded files.
	 */
	function get_rocket_exclude_files( $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( 'css' === $extension ) {
			$excluded_files = get_rocket_option( 'exclude_css', array() );
			/**
			 * Filters CSS files to exclude from minification/concatenation.
			 *
			 * @since 2.6
			 *
			 * @param array $excluded_files List of excluded CSS files.
			*/
			$excluded_files = apply_filters( 'rocket_exclude_css', $excluded_files );
		} elseif ( 'js' === $extension ) {
			global $wp_scripts;

			$excluded_files = get_rocket_option( 'exclude_js', array() );

			if ( get_rocket_option( 'defer_all_js', 0 ) && get_rocket_option( 'defer_all_js_safe', 0 ) ) {
				$excluded_files[] = rocket_clean_exclude_file( site_url( $wp_scripts->registered['jquery-core']->src ) );
			}

			/**
			 * Filter JS files to exclude from minification/concatenation.
			 *
			 * @since 2.6
			 *
			 * @param array $js_files List of excluded JS files.
			*/
			$excluded_files = apply_filters( 'rocket_exclude_js', $excluded_files );
		}

		return $excluded_files;
	}
}

if ( ! function_exists( 'rocket_concatenate_google_fonts' ) ) {
	/**
	 * Concatenates Google Fonts tags (http://fonts.googleapis.com/css?...)
	 *
	 * @since 2.3
	 * @deprecated 3.1
	 *
	 * @param string $buffer HTML content.
	 * @return string Modified HTML content
	 */
	function rocket_concatenate_google_fonts( $buffer ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		// Get all Google Fonts CSS files.
		$buffer_without_comments = preg_replace( '/<!--(.*)-->/Uis', '', $buffer );
		preg_match_all( '/<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])((?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!\1).)+)\1)(?:\s+[^>]*)?>/iU', $buffer_without_comments, $matches );

		if ( ! $matches[2] || 1 === count( $matches ) ) {
			return $buffer;
		}

		$fonts   = array();
		$subsets = array();

		foreach ( $matches[2] as $k => $font ) {
			// Get fonts name.
			$font = str_replace( array( '%7C', '%7c' ), '|', $font );
			$font = explode( 'family=', $font );
			$font = ( isset( $font[1] ) ) ? explode( '&', $font[1] ) : array();

			// Add font to the collection.
			$fonts = array_merge( $fonts, explode( '|', reset( $font ) ) );

			// Add subset to collection.
			$subset = ( is_array( $font ) ) ? end( $font ) : '';
			if ( false !== strpos( $subset, 'subset=' ) ) {
				$subset  = explode( 'subset=', $subset );
				$subsets = array_merge( $subsets, explode( ',', $subset[1] ) );
			}

			// Delete the Google Fonts tag.
			$buffer = str_replace( $matches[0][ $k ], '', $buffer );
		}

		// Concatenate fonts tag.
		$subsets = ( $subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $subsets ) ) ) : '';
		$fonts   = implode( '|', array_filter( array_unique( $fonts ) ) );
		$fonts   = str_replace( '|', '%7C', $fonts );

		if ( ! empty( $fonts ) ) {
			$fonts  = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=' . $fonts . $subsets . '" />';
			$buffer = preg_replace( '/<head(.*)>/U', '<head$1>' . $fonts, $buffer, 1 );
		}

		return $buffer;
	}
}

if ( ! function_exists( 'rocket_minify_inline_css' ) ) {
	/**
	 * Minifies inline CSS
	 *
	 * @since 1.1.6
	 * @deprecated 3.1
	 *
	 * @param string $css HTML content.
	 * @return string Updated HTML content
	 */
	function rocket_minify_inline_css( $css ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		$minify = new Minify\CSS( $css );
		return $minify->minify();
	}
}

if ( ! function_exists( 'rocket_minify_inline_js' ) ) {
	/**
	 * Minifies inline JavaScript
	 *
	 * @since 1.1.6
	 * @deprecated 3.1
	 *
	 * @param string $js HTML content.
	 * @return string Updated HTML content
	 */
	function rocket_minify_inline_js( $js ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		$minify = new Minify\JS( $js );
		return $minify->minify();
	}
}

if ( ! function_exists( 'rocket_extract_ie_conditionals' ) ) {
	/**
	 * Extracts IE conditionals tags and replace them with placeholders
	 *
	 * @since 1.0
	 * @deprecated 3.1
	 *
	 * @param string $buffer HTML content.
	 * @return string Updated HTML content
	 */
	function rocket_extract_ie_conditionals( $buffer ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		preg_match_all( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $buffer, $conditionals_match );
		$buffer = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $buffer );

		$conditionals = array();
		foreach ( $conditionals_match[0] as $conditional ) {
			$conditionals[] = $conditional;
		}

		return array( $buffer, $conditionals );
	}
}

if ( ! function_exists( 'rocket_inject_ie_conditionals' ) ) {
	/**
	 * Replaces WP Rocket placeholders with IE condtional tags
	 *
	 * @since 1.0
	 * @deprecated 3.1
	 *
	 * @param string $buffer HTML content.
	 * @param array  $conditionals An array of HTML conditional tags.
	 * @return string Updated HTML content
	 */
	function rocket_inject_ie_conditionals( $buffer, $conditionals ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		foreach ( $conditionals as $conditional ) {
			if ( false !== strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
				$buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/', $conditional, $buffer, 1 );
			} else {
				break;
			}
		}
		return $buffer;
	}
}

if ( ! function_exists( 'rocket_minify_files' ) ) {
	/**
	 * Parses the buffer to minify the CSS and JS files
	 *
	 * @since 2.11
	 * @since 2.1
	 * @deprecated 3.1
	 *
	 * @param string $buffer    HTML output.
	 * @param string $extension Type of files to minify.
	 * @return string Updated HTML output.
	 */
	function rocket_minify_files( $buffer, $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		global $wp_scripts, $rocket_js_enqueued_in_head;
		if ( 'css' === $extension ) {
			$concatenate = get_rocket_option( 'minify_concatenate_css', false ) ? true : false;
			// Get all css files with this regex.
			preg_match_all( apply_filters( 'rocket_minify_css_regex_pattern', '/<link\s*.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"](.+)>/iU' ), $buffer, $tags_match, PREG_SET_ORDER );
		}

		if ( 'js' === $extension ) {
			$js_files_in_head = '';
			$concatenate      = get_rocket_option( 'minify_concatenate_js', false ) ? true : false;
			if ( $rocket_js_enqueued_in_head && is_array( $rocket_js_enqueued_in_head ) ) {
				$js_files_in_head = implode( '|', $rocket_js_enqueued_in_head );
			}

			// Get all js files with this regex.
			preg_match_all( apply_filters( 'rocket_minify_js_regex_pattern', '#<script[^>]+?src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*>(?:<\/script>)#iU' ), $buffer, $tags_match, PREG_SET_ORDER );
		}

		$original_buffer   = $buffer;
		$files             = array();
		$excluded_files    = array();
		$external_js_files = array();

		foreach ( $tags_match as $tag ) {
			// Don't minify external files.
			if ( is_rocket_external_file( $tag[1], $extension ) ) {
				if ( 'js' === $extension && $concatenate ) {
					$host                 = rocket_extract_url_component( $tag[1], PHP_URL_HOST );
					$excluded_external_js = get_rocket_minify_excluded_external_js();
					if ( ! isset( $excluded_external_js[ $host ] ) ) {
						$external_js_files[] = $tag[0];
					}
				}
				continue;
			}

			// Don't minify excluded files.
			if ( is_rocket_minify_excluded_file( $tag, $extension ) ) {
				if ( $concatenate && 'js' === $extension && get_rocket_option( 'defer_all_js' ) && get_rocket_option( 'defer_all_js_safe' ) && false !== strpos( $tag[1], $wp_scripts->registered['jquery-core']->src ) ) {
					if ( get_rocket_option( 'remove_query_strings' ) ) {
						$external_js_files['jquery-cache-busting'] = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'script_loader_src' ), $tag[0] );
						$buffer                                    = str_replace( $tag[0], $external_js_files['jquery-cache-busting'], $buffer );
					} else {
						$external_js_files[] = $tag[0];
					}

					continue;
				}

				$excluded_files[] = $tag;
				continue;
			}

			if ( $concatenate ) {
				if ( 'js' === $extension ) {
					$file_path = rocket_clean_exclude_file( $tag[1] );

					if ( ! empty( $js_files_in_head ) && preg_match( '#(' . $js_files_in_head . ')#', $file_path ) ) {
						$files['header'][] = strtok( $tag[1], '?' );
					} else {
						$files['footer'][] = strtok( $tag[1], '?' );
					}
				} else {
					$files[] = strtok( $tag[1], '?' );
				}

				$buffer = str_replace( $tag[0], '', $buffer );
				continue;
			}

			// Don't minify if file is already minified.
			if ( preg_match( '/(?:-|\.)min.' . $extension . '/iU', $tag[1] ) ) {
				$excluded_files[] = $tag;
				continue;
			}

			// Don't minify jQuery included in WP core since it's already minified but without .min in the filename.
			if ( ! empty( $wp_scripts->registered['jquery-core']->src ) && false !== strpos( $tag[1], $wp_scripts->registered['jquery-core']->src ) ) {
				$excluded_files[] = $tag;
				continue;
			}

			$files[] = $tag;
		}

		if ( get_rocket_option( 'remove_query_strings' ) ) {
			foreach ( $excluded_files as $tag ) {
				if ( 'css' === $extension ) {
					$tag_cache_busting = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'style_loader_src' ), $tag[0] );
				}

				if ( 'js' === $extension ) {
					$tag_cache_busting = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'script_loader_src' ), $tag[0] );
				}

				$buffer = str_replace( $tag[0], $tag_cache_busting, $buffer );
			}
		}

		if ( empty( $files ) ) {
			return $buffer;
		}

		if ( ! $concatenate ) {
			foreach ( $files as $tag ) {
				$minify_url = get_rocket_minify_url( $tag[1], $extension );

				if ( ! $minify_url ) {
					continue;
				}

				$minify_tag = str_replace( $tag[1], $minify_url, $tag[0] );

				if ( 'css' === $extension ) {
					$minify_tag = str_replace( $tag[2], ' data-minify="1" ' . $tag[2], $minify_tag );
				}

				if ( 'js' === $extension ) {
					$minify_tag = str_replace( '></script>', ' data-minify="1"></script>', $minify_tag );
				}

				$buffer = str_replace( $tag[0], $minify_tag, $buffer );
			}

			return $buffer;
		}

		if ( 'js' === $extension ) {
			$minify_header_url = get_rocket_minify_url( $files['header'], $extension );
			$minify_url        = get_rocket_minify_url( $files['footer'], $extension );

			if ( ! $minify_header_url && ! $minify_url ) {
				return $original_buffer;
			}

			foreach ( $external_js_files as $external_js_file ) {
				$buffer = str_replace( $external_js_file, '', $buffer );
			}

			$minify_header_tag = '<script src="' . $minify_header_url . '" data-minify="1"></script>';
			$buffer            = preg_replace( '/<head(.*)>/U', '<head$1>' . implode( '', $external_js_files ) . $minify_header_tag, $buffer, 1 );

			$minify_tag = '<script src="' . $minify_url . '" data-minify="1"></script>';
			return str_replace( '</body>', $minify_tag . '</body>', $buffer );

		}

		if ( 'css' === $extension ) {
			$minify_url = get_rocket_minify_url( $files, $extension );

			if ( ! $minify_url ) {
				return $original_buffer;
			}

			$minify_tag = '<link rel="stylesheet" href="' . $minify_url . '" data-minify="1" />';
			return preg_replace( '/<head(.*)>/U', '<head$1>' . $minify_tag, $buffer, 1 );
		}
	}
}

if ( ! function_exists( 'is_rocket_external_file' ) ) {
	/**
	 * Determines if the file is external
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $url       URL of the file.
	 * @param string $extension File extension.
	 * @return bool True if external, false otherwise
	 */
	function is_rocket_external_file( $url, $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		$file       = get_rocket_parse_url( $url );
		$wp_content = get_rocket_parse_url( WP_CONTENT_URL );
		$hosts      = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
		$hosts[]    = $wp_content['host'];
		$langs      = get_rocket_i18n_uri();

		// Get host for all langs.
		if ( $langs ) {
			foreach ( $langs as $lang ) {
				$hosts[] = rocket_extract_url_component( $lang, PHP_URL_HOST );
			}
		}

		$hosts_index = array_flip( array_unique( $hosts ) );

		// URL has domain and domain is not part of the internal domains.
		if ( isset( $file['host'] ) && ! empty( $file['host'] ) && ! isset( $hosts_index[ $file['host'] ] ) ) {
			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		if ( ! isset( $file['host'] ) && ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'is_rocket_minify_excluded_file' ) ) {
	/**
	 * Determines if it is a file excluded from minification
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param array  $tag       Array containing the matches from the regex.
	 * @param string $extension File extension.
	 * @return bool True if it is a file excluded, false otherwise
	 */
	function is_rocket_minify_excluded_file( $tag, $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		// File should not be minified.
		if ( false !== strpos( $tag[0], 'data-minify=' ) || false !== strpos( $tag[0], 'data-no-minify=' ) ) {
			return true;
		}

		if ( 'css' === $extension ) {
			// CSS file media attribute is not all or screen.
			if ( false !== strpos( $tag[0], 'media=' ) && ! preg_match( '/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/iU', $tag[0] ) ) {
				return true;
			}

			if ( false !== strpos( $tag[0], 'only screen and' ) ) {
				return true;
			}
		}

		$file_path = rocket_extract_url_component( $tag[1], PHP_URL_PATH );

		// File extension is not .css or .js.
		if ( pathinfo( $file_path, PATHINFO_EXTENSION ) !== $extension ) {
			return true;
		}

		$excluded_files = get_rocket_exclude_files( $extension );

		if ( ! empty( $excluded_files ) ) {
			foreach ( $excluded_files as $i => $excluded_file ) {
				$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
			}

			$excluded_files = implode( '|', $excluded_files );

			// File is excluded from minification/concatenation.
			if ( preg_match( '#^(' . $excluded_files . ')$#', $file_path ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'get_rocket_minify_url' ) ) {
	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string|array $files     Original file(s) URL(s).
	 * @param string       $extension File(s) extension.
	 * @return string|bool The minify URL if successful, false otherwise
	 */
	function get_rocket_minify_url( $files, $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( empty( $files ) ) {
			return false;
		}

		$hosts         = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
		$hosts['home'] = rocket_extract_url_component( home_url(), PHP_URL_HOST );
		$hosts_index   = array_flip( $hosts );
		$minify_key    = get_rocket_option( 'minify_' . $extension . '_key', create_rocket_uniqid() );

		if ( is_string( $files ) ) {
			$file      = get_rocket_parse_url( $files );
			$file_path = rocket_url_to_path( strtok( $files, '?' ), $hosts_index );
			$unique_id = md5( $files . $minify_key );
			$filename  = preg_replace( '/\.(' . $extension . ')$/', '-' . $unique_id . '.' . $extension, ltrim( rocket_realpath( $file['path'] ), '/' ) );
		} else {
			foreach ( $files as $file ) {
				$file_path[] = rocket_url_to_path( $file, $hosts_index );
			}

			$files_hash = implode( ',', $files );
			$filename   = md5( $files_hash . $minify_key ) . '.' . $extension;
		}

		$minified_file = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/' . $filename;

		if ( ! file_exists( $minified_file ) ) {
			$minified_content = rocket_minify( $file_path, $extension );

			if ( ! $minified_content ) {
				return false;
			}

			$minify_filepath = rocket_write_minify_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				return false;
			}
		}

		$minify_url = get_rocket_cdn_url( WP_ROCKET_MINIFY_CACHE_URL . get_current_blog_id() . '/' . $filename, array( 'all', 'css_and_js', $extension ) );

		if ( 'css' === $extension ) {
			/**
			 * Filters CSS file URL with CDN hostname
			 *
			 * @since 2.1
			 *
			 * @param string $minify_url Minified file URL.
			*/
			return apply_filters( 'rocket_css_url', $minify_url );
		}

		if ( 'js' === $extension ) {
			/**
			 * Filters JavaScript file URL with CDN hostname
			 *
			 * @since 2.1
			 *
			 * @param string $minify_url Minified file URL.
			*/
			return apply_filters( 'rocket_js_url', $minify_url );

		}
	}
}

if ( ! function_exists( 'rocket_minify' ) ) {
	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string|array $files     File(s) to minify.
	 * @param string       $extension File(s) extension.
	 * @return string|bool Minified content, false if empty
	 */
	function rocket_minify( $files, $extension ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( 'css' === $extension ) {
			$minify = new Minify\CSS();
		} elseif ( 'js' === $extension ) {
			$minify = new Minify\JS();
		}

		$files = (array) $files;

		foreach ( $files as $file ) {
			$file_content = rocket_direct_filesystem()->get_contents( $file );
			if ( 'css' === $extension ) {
				/**
				 * Filters the Document Root path to use during CSS minification to rewrite paths
				 *
				 * @since 2.7
				 *
				 * @param string The Document Root path.
				*/
				$document_root = apply_filters( 'rocket_min_documentRoot', ABSPATH );

				$file_content = rocket_cdn_css_properties( Minify_CSS_UriRewriter::rewrite( $file_content, dirname( $file ), $document_root ) );
			}

			$minify->add( $file_content );
		}

		$minified_content = $minify->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}
}

if ( ! function_exists( 'rocket_write_minify_file' ) ) {
	/**
	 * Writes the minified content to a file
	 *
	 * @since 2.11
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $content       Minified content.
	 * @param string $minified_file Path to the minified file to write in.
	 * @return bool True if successful, false otherwise
	 */
	function rocket_write_minify_file( $content, $minified_file ) {
		_deprecated_function( __FUNCTION__, '3.1' );

		if ( file_exists( $minified_file ) ) {
			return true;
		}

		if ( ! rocket_mkdir_p( dirname( $minified_file ) ) ) {
			return false;
		}

		return rocket_put_content( $minified_file, $content );
	}
}

if ( ! function_exists( 'get_rocket_minify_excluded_external_js' ) ) {
	/**
	 * Get all JS externals files to exclude of the minification process
	 *
	 * @since 2.6
	 * @deprecated 3.1
	 *
	 * @return array Array of excluded external JS
	 */
	function get_rocket_minify_excluded_external_js() {
		_deprecated_function( __FUNCTION__, '3.1' );

		/**
		 * Filters JS externals files to exclude from the minification process (do not move into the header)
		 *
		 * @since 2.2
		 *
		 * @param array $hostnames Hostname of JS files to exclude.
		 */
		$excluded_external_js = apply_filters(
			'rocket_minify_excluded_external_js', array(
				'forms.aweber.com',
				'video.unrulymedia.com',
				'gist.github.com',
				'stats.wp.com',
				'stats.wordpress.com',
				'www.statcounter.com',
				'widget.rafflecopter.com',
				'widget-prime.rafflecopter.com',
				'widget.supercounters.com',
				'releases.flowplayer.org',
				'tools.meetaffiliate.com',
				'c.ad6media.fr',
				'cdn.stickyadstv.com',
				'www.smava.de',
				'contextual.media.net',
				'app.getresponse.com',
				'ap.lijit.com',
				'adserver.reklamstore.com',
				's0.wp.com',
				'wprp.zemanta.com',
				'files.bannersnack.com',
				'smarticon.geotrust.com',
				'js.gleam.io',
				'script.ioam.de',
				'ir-na.amazon-adsystem.com',
				'web.ventunotech.com',
				'verify.authorize.net',
				'ads.themoneytizer.com',
				'embed.finanzcheck.de',
				'imagesrv.adition.com',
				'js.juicyads.com',
				'form.jotformeu.com',
				'speakerdeck.com',
				'content.jwplatform.com',
				'ads.investingchannel.com',
				'app.ecwid.com',
				'www.industriejobs.de',
				's.gravatar.com',
				'cdn.jsdelivr.net',
				'cdnjs.cloudflare.com',
				'code.jquery.com',
			)
		);

		return array_flip( $excluded_external_js );
	}
}

if ( ! function_exists( 'rocket_cache_dynamic_resource' ) ) {
	/**
	 * Create a static file for dynamically generated CSS/JS from PHP
	 *
	 * @since 2.9
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $src dynamic CSS/JS file URL.
	 * @return string URL of the generated static file
	 */
	function rocket_cache_dynamic_resource( $src ) {
		_deprecated_function( __FUNCTION__, '3.1' );
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
}

if ( ! function_exists( 'rocket_browser_cache_busting' ) ) {
	/**
	 * Wrapper for get_rocket_browser_cache_busting except when minification is active.
	 *
	 * @since 2.9
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $src CSS/JS file URL.
	 * @return string updated CSS/JS file URL.
	 */
	function rocket_browser_cache_busting( $src ) {
		_deprecated_function( __FUNCTION__, '3.1' );
		$current_filter = current_filter();

		if ( 'style_loader_src' === $current_filter && get_rocket_option( 'minify_css' ) && ( ! defined( 'DONOTMINIFYCSS' ) || ! DONOTMINIFYCSS ) && ! is_rocket_post_excluded_option( 'minify_css' ) ) {
			return $src;
		}

		if ( 'script_loader_src' === $current_filter && get_rocket_option( 'minify_js' ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
			return $src;
		}

		return get_rocket_browser_cache_busting( $src, $current_filter );
	}
}

if ( ! function_exists( 'get_rocket_browser_cache_busting' ) ) {
	/**
	 * Create a cache busting file with the version in the filename
	 *
	 * @since 2.9
	 * @deprecated 3.1
	 * @author Remy Perona
	 *
	 * @param string $src CSS/JS file URL.
	 * @param string $current_filter Current WordPress filter.
	 * @return string updated CSS/JS file URL
	 */
	function get_rocket_browser_cache_busting( $src, $current_filter = '' ) {
		_deprecated_function( __FUNCTION__, '3.1' );
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
}

if ( ! function_exists( 'rocket_dns_prefetch_buffer' ) ) {
	/**
	 * Inserts html code for domain names to DNS prefetch in the buffer before creating the cache file
	 *
	 * @since 2.0
	 * @deprecated 3.1
	 * @author Jonathan Buttigieg
	 *
	 * @param String $buffer HTML content.
	 * @return String Updated buffer
	 */
	function rocket_dns_prefetch_buffer( $buffer ) {
		_deprecated_function( __FUNCTION__, '3.1' );
		$dns_link_tags = '';
		$domains       = rocket_get_dns_prefetch_domains();

		if ( (bool) $domains ) {
			foreach ( $domains as $domain ) {
				$dns_link_tags .= '<link rel="dns-prefetch" href="' . esc_url( $domain ) . '" />';
			}
		}

		$old_ie_conditional_tag = '';

		/**
		 * Allow to print an empty IE conditional tag to speed up old IE versions to load CSS & JS files
		 *
		 * @since 2.6.5
		 *
		 * @param bool true will print the IE conditional tag
		 */
		if ( apply_filters( 'do_rocket_old_ie_prefetch_conditional_tag', true ) ) {
			$old_ie_conditional_tag = '<!--[if IE]><![endif]-->';
		}

		// Insert all DNS prefecth tags in head.
		$buffer = preg_replace( '/<head(.*)>/', '<head$1>' . $old_ie_conditional_tag . $dns_link_tags, $buffer, 1 );

		return $buffer;
	}
}
