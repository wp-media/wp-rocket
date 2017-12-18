<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * A wrapper to easily get rocket option
 *
 * @since 1.3.0
 *
 * @param string $option  The option name.
 * @param bool   $default (default: false) The default value of option.
 * @return mixed The option value
 */
function get_rocket_option( $option, $default = false ) {
	/**
	 * Pre-filter any WP Rocket option before read
	 *
	 * @since 2.5
	 *
	 * @param variant $default The default value
	*/
	$value = apply_filters( 'pre_get_rocket_option_' . $option, null, $default );
	if ( null !== $value ) {
		return $value;
	}
	$options = get_option( WP_ROCKET_SLUG );
	if ( 'consumer_key' === $option && defined( 'WP_ROCKET_KEY' ) ) {
		return WP_ROCKET_KEY;
	} elseif ( 'consumer_email' === $option && defined( 'WP_ROCKET_EMAIL' ) ) {
		return WP_ROCKET_EMAIL;
	}

	$value = isset( $options[ $option ] ) && '' !== $options[ $option ] ? $options[ $option ] : $default;

	/**
	 * Filter any WP Rocket option after read
	 *
	 * @since 2.5
	 *
	 * @param variant $default The default value
	*/
	return apply_filters( 'get_rocket_option_' . $option, $value, $default );
}

/**
 * Update a WP Rocket option.
 *
 * @since 2.7
 *
 * @param  string $key    The option name.
 * @param  string $value  The value of the option.
 * @return void
 */
function update_rocket_option( $key, $value ) {
	$options         = get_option( WP_ROCKET_SLUG );
	$options[ $key ] = $value;

	update_option( WP_ROCKET_SLUG, $options );
}

/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 *
 * @param string $plugin Plugin folder/main file.
 */
function rocket_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || rocket_is_plugin_active_for_network( $plugin );
}

/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 *
 * @param string $plugin Plugin folder/main file.
 */
function rocket_is_plugin_active_for_network( $plugin ) {
	if ( ! is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	if ( isset( $plugins[ $plugin ] ) ) {
		return true;
	}

	return false;
}

/**
 * Is we need to exclude some specifics options on a post.
 *
 * @since 2.5
 *
 * @param  string $option  The option name (lazyload, css, js, cdn).
 * @return bool            True if the option is deactivated
 */
function is_rocket_post_excluded_option( $option ) {
	global $post;

	if ( ! is_object( $post ) ) {
		return false;
	}

	if ( is_home() ) {
		$post_id = get_queried_object_id();
	}

	if ( is_singular() && isset( $post ) ) {
		$post_id = $post->ID;
	}

	return ( isset( $post_id ) ) ? get_post_meta( $post_id, '_rocket_exclude_' . $option, true ) : false;
}

/**
 * Check if we need to cache the mobile version of the website (if available)
 *
 * @since 1.0
 *
 * @return bool True if option is activated
 */
function is_rocket_cache_mobile() {
	return get_rocket_option( 'cache_mobile', false );
}

/**
 * Check if we need to generate a different caching file for mobile (if available)
 *
 * @since 2.7
 *
 * @return bool True if option is activated
 */
function is_rocket_generate_caching_mobile_files() {
	return get_rocket_option( 'do_caching_mobile_files', false );
}

/**
 * Check if we need to cache SSL requests of the website (if available)
 *
 * @since 1.0
 * @access public
 * @return bool True if option is activated
 */
function is_rocket_cache_ssl() {
	return get_rocket_option( 'cache_ssl', false );
}

/**
 * Check if we need to disable CDN on SSL pages
 *
 * @since 2.5
 * @access public
 * @return bool True if option is activated
 */
function is_rocket_cdn_on_ssl() {
	return is_ssl() && get_rocket_option( 'cdn_ssl', 0 ) ? false : true;
}

/**
 * Get the domain names to DNS prefetch from WP Rocket options
 *
 * @since 2.8.9
 * @author Remy Perona
 *
 * return Array An array of domain names to DNS prefetch
 */
function rocket_get_dns_prefetch_domains() {
	$cdn_cnames = get_rocket_cdn_cnames( array( 'all', 'images', 'css_and_js', 'css', 'js' ) );

	// Don't add CNAMES if CDN is disabled HTTPS pages or on specific posts.
	if ( ! is_rocket_cdn_on_ssl() || is_rocket_post_excluded_option( 'cdn' ) ) {
		$cdn_cnames = array();
	}

	$domains = array_merge( $cdn_cnames, (array) get_rocket_option( 'dns_prefetch' ) );

	/**
	 * Filter list of domains to prefetch DNS
	 *
	 * @since 1.1.0
	 *
	 * @param array $domains List of domains to prefetch DNS
	 */
	return apply_filters( 'rocket_dns_prefetch', $domains );
}

/**
 * Get the interval task cron purge in seconds
 * This setting can be changed from the options page of the plugin
 *
 * @since 1.0
 *
 * @return int The interval task cron purge in seconds
 */
function get_rocket_purge_cron_interval() {
	if ( ! get_rocket_option( 'purge_cron_interval' ) || ! get_rocket_option( 'purge_cron_unit' ) ) {
		return 0;
	}
	return (int) ( get_rocket_option( 'purge_cron_interval' ) * constant( get_rocket_option( 'purge_cron_unit' ) ) );
}

/**
 * Get all uri we don't cache
 *
 * @since 2.6   Using json_get_url_prefix() to auto-exclude the WordPress REST API
 * @since 2.4.1 Auto-exclude WordPress REST API
 * @since 2.0
 *
 * @return array List of rejected uri
 */
function get_rocket_cache_reject_uri() {
	$uri = get_rocket_option( 'cache_reject_uri', array() );

	// Exclude feeds.
	$uri[] = '(.*)/' . $GLOBALS['wp_rewrite']->feed_base . '/?';

	/**
	 * Filter the rejected uri
	 *
	 * @since 2.1
	 *
	 * @param array $uri List of rejected uri
	*/
	$uri = apply_filters( 'rocket_cache_reject_uri', $uri );

	$uri = implode( '|', array_filter( $uri ) );
	return $uri;
}

/**
 * Get all cookie names we don't cache
 *
 * @since 2.0
 *
 * @return array List of rejected cookies
 */
function get_rocket_cache_reject_cookies() {
	$cookies   = get_rocket_option( 'cache_reject_cookies', array() );
	$cookies[] = str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE );
	$cookies[] = 'wp-postpass_';
	$cookies[] = 'wptouch_switch_toggle';
	$cookies[] = 'comment_author_';
	$cookies[] = 'comment_author_email_';

	/**
	 * Filter the rejected cookies
	 *
	 * @since 2.1
	 *
	 * @param array $cookies List of rejected cookies
	*/
	$cookies = apply_filters( 'rocket_cache_reject_cookies', $cookies );

	$cookies = implode( '|', array_filter( $cookies ) );
	return $cookies;
}

/**
 * Get list of mandatory cookies to be able to cache pages.
 *
 * @since 2.7
 *
 * @return array List of mandatory cookies.
 */
function get_rocket_cache_mandatory_cookies() {
	$cookies = array();

	/**
	 * Filter list of mandatory cookies
	 *
	 * @since 2.7
	 *
	 * @param array List of mandatory cookies
	 */
	$cookies = apply_filters( 'rocket_cache_mandatory_cookies', $cookies );
	$cookies = array_filter( $cookies );

	$cookies = implode( '|', $cookies );
	return $cookies;
}

/**
 * Get list of dynamic cookies.
 *
 * @since 2.7
 *
 * @return array List of dynamic cookies.
 */
function get_rocket_cache_dynamic_cookies() {
	$cookies = array();

	/**
	 * Filter list of dynamic cookies
	 *
	 * @since 2.7
	 *
	 * @param array List of dynamic cookies
	 */
	$cookies = apply_filters( 'rocket_cache_dynamic_cookies', $cookies );
	$cookies = array_filter( $cookies );

	return $cookies;
}

/**
 * Get all User-Agent we don't allow to get cache files
 *
 * @since 2.3.5
 *
 * @return array List of rejected User-Agent
 */
function get_rocket_cache_reject_ua() {
	$ua   = get_rocket_option( 'cache_reject_ua', array() );
	$ua[] = 'facebookexternalhit';

	/**
	 * Filter the rejected User-Agent
	 *
	 * @since 2.3.5
	 *
	 * @param array $ua List of rejected User-Agent
	*/
	$ua = apply_filters( 'rocket_cache_reject_ua', $ua );

	$ua = implode( '|', array_filter( $ua ) );
	$ua = str_replace( array( ' ', '\\\\ ' ), '\\ ', $ua );

	return $ua;
}

/**
 * Get all files we don't allow to get in CDN
 *
 * @since 2.5
 *
 * @return array List of rejected files
 */
function get_rocket_cdn_reject_files() {
	$files = get_rocket_option( 'cdn_reject_files', array() );

	/**
	 * Filter the rejected files
	 *
	 * @since 2.5
	 *
	 * @param array $files List of rejected files
	*/
	$files = apply_filters( 'rocket_cdn_reject_files', $files );

	$files = implode( '|', array_filter( $files ) );

	return $files;
}

/**
 * Get all CNAMES
 *
 * @since 2.1
 *
 * @param string $zone (default: 'all') List of zones.
 * @return array List of CNAMES
 */
function get_rocket_cdn_cnames( $zone = 'all' ) {
	if ( (int) get_rocket_option( 'cdn' ) === 0 ) {
		return array();
	}

	$hosts       = array();
	$cnames      = get_rocket_option( 'cdn_cnames', array() );
	$cnames_zone = get_rocket_option( 'cdn_zone', array() );
	$zone        = is_array( $zone ) ? $zone : (array) $zone;

	foreach ( $cnames as $k => $_urls ) {
		if ( in_array( $cnames_zone[ $k ], $zone, true ) ) {
			$_urls = explode( ',', $_urls );
			$_urls = array_map( 'trim', $_urls );

			foreach ( $_urls as $url ) {
				$hosts[] = $url;
			}
		}
	}

	/**
	 * Filter all CNAMES.
	 *
	 * @since 2.7
	 *
	 * @param array $hosts List of CNAMES.
	*/
	$hosts = apply_filters( 'rocket_cdn_cnames', $hosts );
	$hosts = array_filter( $hosts );

	return $hosts;
}

/**
 * Get all query strings which can be cached.
 *
 * @since 2.3
 *
 * @return array List of query strings which can be cached.
 */
function get_rocket_cache_query_string() {
	$query_strings = get_rocket_option( 'cache_query_strings', array() );

	/**
	 * Filter query strings which can be cached.
	 *
	 * @since 2.3
	 *
	 * @param array $query_strings List of query strings which can be cached.
	*/
	$query_strings = apply_filters( 'rocket_cache_query_strings', $query_strings );

	return $query_strings;
}

/**
 * Get all files to exclude from minification/concatenation.
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string $extension Type of files to exclude.
 * @return array Array of excluded files.
 */
function get_rocket_exclude_files( $extension ) {
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

/**
 * Get list of JS files to be excluded from defer JS.
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return array An array of URLs for the JS files to be excluded.
 */
function get_rocket_exclude_defer_js() {
	global $wp_scripts;

	$exclude_defer_js = array();

	if ( get_rocket_option( 'defer_all_js', 0 ) && get_rocket_option( 'defer_all_js_safe', 0 ) ) {
		$jquery = site_url( $wp_scripts->registered['jquery-core']->src );

		if ( get_rocket_option( 'remove_query_strings', 0 ) ) {
			$jquery = get_rocket_browser_cache_busting( $jquery . '?ver=' . $wp_scripts->registered['jquery-core']->ver, 'script_loader_src' );
		}

		$exclude_defer_js[] = rocket_clean_exclude_file( $jquery );
	}

	/**
	 * Filter list of Deferred JavaScript files
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $exclude_defer_js An array of URLs for the JS files to be excluded.
	 */
	$exclude_defer_js = apply_filters( 'rocket_exclude_defer_js', $exclude_defer_js );

	return $exclude_defer_js;
}

/**
 * Get list of CSS files to be excluded from async CSS.
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return array An array of URLs for the CSS files to be excluded.
 */
function get_rocket_exclude_async_css() {
	/**
	 * Filter list of async CSS files
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $exclude_async_css An array of URLs for the CSS files to be excluded.
	 */
	$exclude_async_css = apply_filters( 'rocket_exclude_async_css', array() );

	return $exclude_async_css;
}

/**
 * Determine if the key is valid
 *
 * @since 2.9 use hash_equals() to compare the hash values
 * @since 1.0
 *
 * @return bool true if everything is ok, false otherwise
 */
function rocket_valid_key() {
	$rocket_secret_key = get_rocket_option( 'secret_key' );
	if ( ! $rocket_secret_key ) {
		return false;
	}

	return 8 === strlen( get_rocket_option( 'consumer_key' ) ) && hash_equals( $rocket_secret_key, hash( 'crc32', get_rocket_option( 'consumer_email' ) ) );
}

/**
 * Determine if the key is valid.
 *
 * @since 2.9.7 Remove arguments ($type & $data).
 * @since 2.9.7 Stop to auto-check the validation each 1 & 30 days.
 * @since 2.2 The function do the live check and update the option.
 */
function rocket_check_key() {
	// Recheck the license.
	$return = rocket_valid_key();

	if ( ! rocket_valid_key() ) {
		$response = wp_remote_get(
			WP_ROCKET_WEB_VALID, array(
				'timeout' => 30,
			)
		);

		$json           = ! is_wp_error( $response ) ? json_decode( $response['body'] ) : false;
		$rocket_options = array();

		if ( $json ) {
			$rocket_options['consumer_key']   = $json->data->consumer_key;
			$rocket_options['consumer_email'] = $json->data->consumer_email;

			if ( $json->success ) {
				$rocket_options['secret_key'] = $json->data->secret_key;

				if ( ! get_rocket_option( 'license' ) ) {
					$rocket_options['license'] = '1';
				}
			} else {
				$messages = array(
					'BAD_LICENSE' => __( 'Your license is not valid.', 'rocket' ),
					'BAD_NUMBER'  => __( 'You cannot add more websites. Upgrade your account.', 'rocket' ),
					'BAD_SITE'    => __( 'This website is not allowed.', 'rocket' ),
					'BAD_KEY'     => __( 'This license key is not accepted.', 'rocket' ),
				);

				$rocket_options['secret_key'] = '';

				add_settings_error( 'general', 'settings_updated', $messages[ $json->data->reason ], 'error' );
			}

			set_transient( WP_ROCKET_SLUG, $rocket_options );
			$return = (array) $rocket_options;
		}
	}

	return $return;
}
