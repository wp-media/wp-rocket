<?php
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * A wrapper to easily get rocket option
 *
 * @since 3.0 Use the new options classes
 * @since 1.3.0
 *
 * @param string $option  The option name.
 * @param bool   $default (default: false) The default value of option.
 * @return mixed The option value
 */
function get_rocket_option( $option, $default = false ) {
	$options_api = new Options( 'wp_rocket_' );
	$options     = new Options_Data( $options_api->get( 'settings', array() ) );

	return $options->get( $option, $default );
}

/**
 * Update a WP Rocket option.
 *
 * @since 3.0 Use the new options classes
 * @since 2.7
 *
 * @param  string $key    The option name.
 * @param  string $value  The value of the option.
 * @return void
 */
function update_rocket_option( $key, $value ) {
	$options_api = new Options( 'wp_rocket_' );
	$options     = new Options_Data( $options_api->get( 'settings', array() ) );

	$options->set( $key, $value );
	$options_api->set( 'settings', $options->get_options() );
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
 * @return bool True if option is activated and if mobile caching is enabled
 */
function is_rocket_generate_caching_mobile_files() {
	return get_rocket_option( 'cache_mobile', false ) && get_rocket_option( 'do_caching_mobile_files', false );
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

	// Don't add CNAMES if CDN is disabled.
	if ( ! get_rocket_option( 'cdn' ) || is_rocket_post_excluded_option( 'cdn' ) ) {
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
 * Get all uri we don't cache.
 *
 * @since 2.6   Using json_get_url_prefix() to auto-exclude the WordPress REST API.
 * @since 2.4.1 Auto-exclude WordPress REST API.
 * @since 2.0
 *
 * @return string A pipe separated list of rejected uri.
 */
function get_rocket_cache_reject_uri() {
	$uris      = get_rocket_option( 'cache_reject_uri', array() );
	$home_root = rocket_get_home_dirname();

	if ( '' !== $home_root ) {
		// The site is not at the domain root, it's in a folder.
		$home_root_escaped = preg_quote( $home_root, '/' );
		$home_root_len     = strlen( $home_root );

		foreach ( $uris as $i => $uri ) {
			/**
			 * Since these URIs can be regex patterns like `/homeroot(/.+)/`, we can't simply search for the string `/homeroot/` (nor `/homeroot`).
			 * So this pattern searchs for `/homeroot/` and `/homeroot(/`.
			 */
			if ( ! preg_match( '/' . $home_root_escaped . '\(?\//', $uri ) ) {
				// Reject URIs located outside site's folder.
				unset( $uris[ $i ] );
				continue;
			}

			// Remove the home directory.
			$uris[ $i ] = substr( $uri, $home_root_len );
		}
	}

	// Exclude feeds.
	$uris[] = '/(.+/)?' . $GLOBALS['wp_rewrite']->feed_base . '/?';

	/**
	 * Filter the rejected uri
	 *
	 * @since 2.1
	 *
	 * @param array $uris List of rejected uri
	*/
	$uris = apply_filters( 'rocket_cache_reject_uri', $uris );
	$uris = array_filter( $uris );

	if ( ! $uris ) {
		return '';
	}

	if ( '' !== $home_root ) {
		foreach ( $uris as $i => $uri ) {
			if ( preg_match( '/' . $home_root_escaped . '\(?\//', $uri ) ) {
				// Remove the home directory from the new URIs.
				$uris[ $i ] = substr( $uri, $home_root_len );
			}
		}
	}

	$uris = implode( '|', $uris );

	if ( '' !== $home_root ) {
		// Add the home directory back.
		$uris = $home_root . '(' . $uris . ')';
	}

	return $uris;
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
 * Get all files we don't allow to get in CDN.
 *
 * @since 2.5
 *
 * @return string A pipe-separated list of rejected files.
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

	return implode( '|', array_filter( $files ) );
}

/**
 * Get all CNAMES.
 *
 * @since 2.1
 * @since 3.0 Don't check for WP Rocket CDN option activated to be able to use the function on Hosting with CDN auto-enabled.
 *
 * @param  string $zone List of zones. Default is 'all'.
 * @return array        List of CNAMES
 */
function get_rocket_cdn_cnames( $zone = 'all' ) {
	$hosts  = [];
	$cnames = get_rocket_option( 'cdn_cnames', [] );

	if ( $cnames ) {
		$cnames_zone = get_rocket_option( 'cdn_zone', [] );
		$zone        = (array) $zone;

		foreach ( $cnames as $k => $_urls ) {
			if ( ! in_array( $cnames_zone[ $k ], $zone, true ) ) {
				continue;
			}

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
 * Get list of JS files to be excluded from defer JS.
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return array An array of URLs for the JS files to be excluded.
 */
function get_rocket_exclude_defer_js() {
	global $wp_scripts;

	$exclude_defer_js = [
		'gist.github.com',
		'content.jwplatform.com',
	];

	if ( get_rocket_option( 'defer_all_js', 0 ) && get_rocket_option( 'defer_all_js_safe', 0 ) ) {
		$jquery = site_url( $wp_scripts->registered['jquery-core']->src );

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

	foreach ( $exclude_defer_js as $i => $exclude ) {
		$exclude_defer_js[ $i ] = str_replace( '#', '\#', $exclude );
	}

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
		Logger::info( 'LICENSE VALIDATION PROCESS STARTED.', [ 'license validation process' ] );

		$response = wp_remote_get(
			WP_ROCKET_WEB_VALID, array(
				'timeout' => 30,
			)
		);

		$body           = wp_remote_retrieve_body( $response );
		$json           = json_decode( $body );
		$rocket_options = array();

		if ( $json ) {
			$rocket_options['consumer_key']   = $json->data->consumer_key;
			$rocket_options['consumer_email'] = $json->data->consumer_email;

			if ( $json->success ) {
				$rocket_options['secret_key'] = $json->data->secret_key;

				if ( ! get_rocket_option( 'license' ) ) {
					$rocket_options['license'] = '1';
				}

				Logger::info( 'License validation succeeded.', [ 'license validation process' ] );
			} else {
				$messages = array(
					'BAD_LICENSE' => __( 'Your license is not valid.', 'rocket' ),
					'BAD_NUMBER'  => __( 'You cannot add more websites. Upgrade your account.', 'rocket' ),
					'BAD_SITE'    => __( 'This website is not allowed.', 'rocket' ),
					'BAD_KEY'     => __( 'This license key is not accepted.', 'rocket' ),
				);

				$rocket_options['secret_key'] = '';

				add_settings_error( 'general', 'settings_updated', $messages[ $json->data->reason ], 'error' );

				Logger::error( 'License validation failed.', [
					'license validation process',
					'response_error' => $json->data->reason,
				] );
			}

			set_transient( WP_ROCKET_SLUG, $rocket_options );
			$return = (array) $rocket_options;
		} elseif ( is_wp_error( $response ) ) {
			Logger::error( 'License validation failed.', [
				'license validation process',
				'request_error' => $response->get_error_messages(),
			] );
		} elseif ( '' !== $body ) {
			Logger::error( 'License validation failed.', [
				'license validation process',
				'response_body' => $body,
			] );
		} else {
			Logger::error( 'License validation failed. No body available in response.', [ 'license validation process' ] );
		}
	}

	return $return;
}

/**
 * Is WP a MultiSite and a subfolder install?
 *
 * @since  3.1.1
 * @author Grégory Viguier
 *
 * @return bool
 */
function rocket_is_subfolder_install() {
	global $wpdb;
	static $subfolder_install;

	if ( isset( $subfolder_install ) ) {
		return $subfolder_install;
	}

	if ( is_multisite() ) {
		$subfolder_install = ! is_subdomain_install();
	} elseif ( ! is_null( $wpdb->sitemeta ) ) {
		$subfolder_install = ! $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = 1 AND meta_key = 'subdomain_install'" );
	} else {
		$subfolder_install = false;
	}

	return $subfolder_install;
}

/**
 * Get the name of the "home directory", in case the home URL is not at the domain's root.
 * It can be seen like the `RewriteBase` from the .htaccess file, but without the trailing slash.
 *
 * @since  3.1.1
 * @author Grégory Viguier
 *
 * @return string
 */
function rocket_get_home_dirname() {
	static $home_root;

	if ( isset( $home_root ) ) {
		return $home_root;
	}

	$home_root = wp_parse_url( rocket_get_main_home_url() );

	if ( ! empty( $home_root['path'] ) ) {
		$home_root = '/' . trim( $home_root['path'], '/' );
		$home_root = rtrim( $home_root, '/' );
	} else {
		$home_root = '';
	}

	return $home_root;
}

/**
 * Get the URL of the site's root. It corresponds to the main site's home page URL.
 *
 * @since  3.1.1
 * @author Grégory Viguier
 *
 * @return string
 */
function rocket_get_main_home_url() {
	static $root_url;

	if ( isset( $root_url ) ) {
		return $root_url;
	}

	if ( ! is_multisite() || is_main_site() ) {
		$root_url = home_url( '/' );
		return $root_url;
	}

	$current_network = get_network();

	if ( $current_network ) {
		$root_url = set_url_scheme( 'https://' . $current_network->domain . $current_network->path );
		$root_url = trailingslashit( $root_url );
	} else {
		$root_url = home_url( '/' );
	}

	return $root_url;
}
