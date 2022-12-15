<?php
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * A wrapper to easily get rocket option
 *
 * @since 3.0 Use the new options classes
 * @since 1.3.0
 *
 * @param string $option  The option name.
 * @param mixed  $default (default: false) The default value of option.
 * @return mixed The option value
 */
function get_rocket_option( $option, $default = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$options_api = new Options( 'wp_rocket_' );
	$options     = new Options_Data( $options_api->get( 'settings', [] ) );

	return $options->get( $option, $default );
}

/**
 * Export settings into JSON.
 *
 * @return array
 */
function rocket_export_options() {
	$site_name = get_rocket_parse_url( get_home_url() );
	$site_name = $site_name['host'] . $site_name['path'];
	$filename  = sprintf( 'wp-rocket-settings-%s-%s-%s.json', $site_name, date( 'Y-m-d' ), uniqid() ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	return [ $filename, wp_json_encode( get_option( WP_ROCKET_SLUG ), JSON_PRETTY_PRINT ) ]; // do not use get_rocket_option() here.
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
function update_rocket_option( $key, $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$options_api = new Options( 'wp_rocket_' );
	$options     = new Options_Data( $options_api->get( 'settings', [] ) );

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
 *
 * @return boolean true when plugin is active; else false.
 */
function rocket_is_plugin_active( $plugin ) {
	return (
		in_array( $plugin, (array) get_option( 'active_plugins', [] ), true )
		||
		rocket_is_plugin_active_for_network( $plugin )
	);
}

/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 *
 * @param string $plugin Plugin folder/main file.
 *
 * @return bool true if multisite and plugin is active for network; else, false.
 */
function rocket_is_plugin_active_for_network( $plugin ) {
	if ( ! is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	return isset( $plugins[ $plugin ] );
}

/**
 * Is we need to exclude some specifics options on a post.
 *
 * @since 2.5
 *
 * @param  string $option  The option name (lazyload, css, js, cdn).
 * @return bool            True if the option is deactivated
 */
function is_rocket_post_excluded_option( $option ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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
function is_rocket_cache_mobile() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	return get_rocket_option( 'cache_mobile', false );
}

/**
 * Check if we need to generate a different caching file for mobile (if available)
 *
 * @since 2.7
 *
 * @return bool True if option is activated and if mobile caching is enabled
 */
function is_rocket_generate_caching_mobile_files() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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
	$domains = (array) get_rocket_option( 'dns_prefetch' );

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
 * Gets the parameters ignored during caching
 *
 * These parameters are ignored when checking the query string during caching to allow serving the default cache when they are present
 *
 * @since 3.4
 *
 * @return array
 */
function rocket_get_ignored_parameters() {
	/**
	 * Filters the ignored parameters
	 *
	 * @since 3.4
	 *
	 * @param array $params An array of ignored parameters as array keys.
	 */
	return apply_filters( 'rocket_cache_ignored_parameters', [] );
}

/**
 * Get all uri we don't cache.
 *
 * @since 3.3.2 Exclude embedded URLs
 * @since 2.6   Using json_get_url_prefix() to auto-exclude the WordPress REST API.
 * @since 2.4.1 Auto-exclude WordPress REST API.
 * @since 2.0
 *
 * @param bool $force Force the static uris to be reverted to null.
 * @param bool $show_safe_content show sensitive uris.
 * @return string A pipe separated list of rejected uri.
 */
function get_rocket_cache_reject_uri( $force = false, $show_safe_content = true ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	static $uris;
	global $wp_rewrite;

	if ( $force ) {
		$uris = null;
	}
	if ( $uris ) {
		return $uris;
	}

	$uris = (array) get_rocket_option( 'cache_reject_uri', [] );

	$home_root         = rocket_get_home_dirname();
	$home_root_escaped = preg_quote( $home_root, '/' ); // The site is not at the domain root, it's in a folder.
	$home_root_len     = strlen( $home_root );

	if ( '' !== $home_root && $uris ) {
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
	$uris[] = '/(?:.+/)?' . $wp_rewrite->feed_base . '(?:/(?:.+/?)?)?$';

	// Exlude embedded URLs.
	$uris[] = '/(?:.+/)?embed/';

	/**
	 * Filter the rejected uri
	 *
	 * @since 2.1
	 *
	 * @param array $uris List of rejected uri
	 * @param bool $show_safe_content show sensitive uris.
	*/
	$uris = apply_filters( 'rocket_cache_reject_uri', $uris, $show_safe_content );
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
 * Get all cookie names we don't cache.
 *
 * @since 2.0
 *
 * @return string A pipe separated list of rejected cookies.
 */
function get_rocket_cache_reject_cookies() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$logged_in_cookie = explode( COOKIEHASH, LOGGED_IN_COOKIE );
	$logged_in_cookie = array_map( 'preg_quote', $logged_in_cookie );
	$logged_in_cookie = implode( '.+', $logged_in_cookie );

	$cookies   = get_rocket_option( 'cache_reject_cookies', [] );
	$cookies[] = $logged_in_cookie;
	$cookies[] = 'wp-postpass_';
	$cookies[] = 'wptouch_switch_toggle';
	$cookies[] = 'comment_author_';
	$cookies[] = 'comment_author_email_';

	/**
	 * Filter the rejected cookies.
	 *
	 * @since 2.1
	 *
	 * @param array $cookies List of rejected cookies.
	*/
	$cookies = (array) apply_filters( 'rocket_cache_reject_cookies', $cookies );
	$cookies = array_filter( $cookies );
	$cookies = array_flip( array_flip( $cookies ) );

	return implode( '|', $cookies );
}

/**
 * Get list of mandatory cookies to be able to cache pages.
 *
 * @since 2.7
 *
 * @return string A pipe separated list of mandatory cookies.
 */
function get_rocket_cache_mandatory_cookies() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$cookies = [];

	/**
	 * Filter list of mandatory cookies.
	 *
	 * @since 2.7
	 *
	 * @param array $cookies List of mandatory cookies.
	 */
	$cookies = (array) apply_filters( 'rocket_cache_mandatory_cookies', $cookies );
	$cookies = array_filter( $cookies );
	$cookies = array_flip( array_flip( $cookies ) );

	return implode( '|', $cookies );
}

/**
 * Get list of dynamic cookies.
 *
 * @since 2.7
 *
 * @return array List of dynamic cookies.
 */
function get_rocket_cache_dynamic_cookies() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$cookies = [];

	/**
	 * Filter list of dynamic cookies.
	 *
	 * @since 2.7
	 *
	 * @param array $cookies List of dynamic cookies.
	 */
	$cookies = (array) apply_filters( 'rocket_cache_dynamic_cookies', $cookies );
	$cookies = array_filter( $cookies );
	$cookies = array_unique( $cookies );

	return $cookies;
}

/**
 * Get all User-Agent we don't allow to get cache files.
 *
 * @since 2.3.5
 *
 * @return string A pipe separated list of rejected User-Agent.
 */
function get_rocket_cache_reject_ua() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$ua   = get_rocket_option( 'cache_reject_ua', [] );
	$ua[] = 'facebookexternalhit';
	$ua[] = 'WhatsApp';

	/**
	 * Filter the rejected User-Agent
	 *
	 * @since 2.3.5
	 *
	 * @param array $ua List of rejected User-Agent.
	*/
	$ua = (array) apply_filters( 'rocket_cache_reject_ua', $ua );
	$ua = array_filter( $ua );
	$ua = array_flip( array_flip( $ua ) );
	$ua = implode( '|', $ua );

	return str_replace( [ ' ', '\\\\ ' ], '\\ ', $ua );
}

/**
 * Get all query strings which can be cached.
 *
 * @since 2.3
 *
 * @return array List of query strings which can be cached.
 */
function get_rocket_cache_query_string() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$query_strings = get_rocket_option( 'cache_query_strings', [] );

	/**
	 * Filter query strings which can be cached.
	 *
	 * @since 2.3
	 *
	 * @param array $query_strings List of query strings which can be cached.
	*/
	$query_strings = (array) apply_filters( 'rocket_cache_query_strings', $query_strings );
	$query_strings = array_filter( $query_strings );
	$query_strings = array_flip( array_flip( $query_strings ) );

	return $query_strings;
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
	$rocket_secret_key = (string) get_rocket_option( 'secret_key', '' );
	if ( ! $rocket_secret_key ) {
		return false;
	}

	$valid_details = 8 === strlen( (string) get_rocket_option( 'consumer_key', '' ) ) && hash_equals( $rocket_secret_key, hash( 'crc32', get_rocket_option( 'consumer_email', '' ) ) );

	if ( ! $valid_details ) {
		set_transient(
			'rocket_check_key_errors',
			[
				__( 'The provided license data are not valid.', 'rocket' ) .
				' <br>' .
				// Translators: %1$s = opening link tag, %2$s = closing link tag.
				sprintf( __( 'To resolve, please %1$scontact support%2$s.', 'rocket' ), '<a href="https://wp-rocket.me/support/" rel="noopener noreferrer" target=_"blank">', '</a>' ),
			]
		);

		return $valid_details;
	}

	return $valid_details;
}

/**
 * Determine if the key is valid.
 *
 * @since 2.9.7 Remove arguments ($type & $data).
 * @since 2.9.7 Stop to auto-check the validation each 1 & 30 days.
 * @since 2.2 The function do the live check and update the option.
 *
 * @return bool|array
 */
function rocket_check_key() {
	// Recheck the license.
	$return = rocket_valid_key();

	if ( $return ) {
		rocket_delete_licence_data_file();

		return $return;
	}

	Logger::info( 'LICENSE VALIDATION PROCESS STARTED.', [ 'license validation process' ] );

	$response = wp_remote_get(
		rocket_get_constant( 'WP_ROCKET_WEB_VALID' ),
		[
			'timeout' => 30,
		]
	);

	if ( is_wp_error( $response ) ) {
		Logger::error(
			'License validation failed.',
			[
				'license validation process',
				'request_error' => $response->get_error_messages(),
			]
		);

		set_transient( 'rocket_check_key_errors', $response->get_error_messages() );

		return $return;
	}

	$body = wp_remote_retrieve_body( $response );
	$json = json_decode( $body );

	if ( null === $json ) {
		if ( '' === $body ) {
			Logger::error( 'License validation failed. No body available in response.', [ 'license validation process' ] );
			// Translators: %1$s = opening em tag, %2$s = closing em tag, %3$s = opening link tag, %4$s closing link tag.
			$message = __( 'License validation failed. Our server could not resolve the request from your website.', 'rocket' ) . '<br>' . sprintf( __( 'Try clicking %1$sValidate License%2$s below. If the error persists, follow %3$sthis guide%4$s.', 'rocket' ), '<em>', '</em>', '<a href="https://docs.wp-rocket.me/article/100-resolving-problems-with-license-validation#general">', '</a>' );
			set_transient( 'rocket_check_key_errors', [ $message ] );

			return $return;
		}

		Logger::error(
			'License validation failed.',
			[
				'license validation process',
				'response_body' => $body,
			]
		);

		if ( 'NULLED' === $body ) {
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			$message = __( 'License validation failed. You may be using a nulled version of the plugin. Please do the following:', 'rocket' ) . '<ul><li>' . sprintf( __( 'Login to your WP Rocket %1$saccount%2$s', 'rocket' ), '<a href="https://wp-rocket.me/account/" rel="noopener noreferrer" target=_"blank">', '</a>' ) . '</li><li>' . __( 'Download the zip file', 'rocket' ) . '<li></li>' . __( 'Reinstall', 'rocket' ) . '</li></ul>' . sprintf( __( 'If you do not have a WP Rocket account, please %1$spurchase a license%2$s.', 'rocket' ), '<a href="https://wp-rocket.me/" rel="noopener noreferrer" target="_blank">', '</a>' );
			set_transient( 'rocket_check_key_errors', [ $message ] );

			return $return;
		}

		if ( 'BAD_USER' === $body ) {
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			$message = __( 'License validation failed. This user account does not exist in our database.', 'rocket' ) . '<br>' . sprintf( __( 'To resolve, please contact support.', 'rocket' ), '<a href="https://wp-rocket.me/support/" rel="noopener noreferrer" target=_"blank">', '</a>' );
			set_transient( 'rocket_check_key_errors', [ $message ] );

			return $return;
		}

		if ( 'USER_BLOCKED' === $body ) {
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			$message = __( 'License validation failed. This user account is blocked.', 'rocket' ) . '<br>' . sprintf( __( 'Please see %1$sthis guide%2$s for more info.', 'rocket' ), '<a href="https://docs.wp-rocket.me/article/100-resolving-problems-with-license-validation#errors" rel="noopener noreferrer" target=_"blank">', '</a>' );
			set_transient( 'rocket_check_key_errors', [ $message ] );

			return $return;
		}

		// Translators: %1$s = opening em tag, %2$s = closing em tag, %3$s = opening link tag, %4$s closing link tag.
		$message = __( 'License validation failed. Our server could not resolve the request from your website.', 'rocket' ) . '<br>' . sprintf( __( 'Try clicking %1$sSave Changes%2$s below. If the error persists, follow %3$sthis guide%4$s.', 'rocket' ), '<em>', '</em>', '<a href="https://docs.wp-rocket.me/article/100-resolving-problems-with-license-validation#general" rel="noopener noreferrer" target=_"blank">', '</a>' );
		set_transient( 'rocket_check_key_errors', [ $message ] );

		return $return;
	}

	$rocket_options                   = [];
	$rocket_options['consumer_key']   = $json->data->consumer_key;
	$rocket_options['consumer_email'] = $json->data->consumer_email;

	if ( ! $json->success ) {
		$messages = [
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			'BAD_LICENSE' => __( 'Your license is not valid.', 'rocket' ) . '<br>' . sprintf( __( 'Make sure you have an active %1$sWP Rocket license%2$s.', 'rocket' ), '<a href="https://wp-rocket.me/" rel="noopener noreferrer" target="_blank">', '</a>' ),
			// Translators: %1$s = opening link tag, %2$s = closing link tag, %3$s = opening link tag.
			'BAD_NUMBER'  => __( 'You have added as many sites as your current license allows.', 'rocket' ) . '<br>' . sprintf( __( 'Upgrade your %1$saccount%2$s or %3$stransfer your license%2$s to this domain.', 'rocket' ), '<a href="https://wp-rocket.me/account/" rel="noopener noreferrer" target=_"blank">', '</a>', '<a href="https://docs.wp-rocket.me/article/28-transfering-your-license-to-another-site" rel="noopener noreferrer" target=_"blank">' ),
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			'BAD_SITE'    => __( 'This website is not allowed.', 'rocket' ) . '<br>' . sprintf( __( 'Please %1$scontact support%2$s.', 'rocket' ), '<a href="https://wp-rocket.me/support/" rel="noopener noreferrer" target=_"blank">', '</a>' ),
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			'BAD_KEY'     => __( 'This license key is not recognized.', 'rocket' ) . '<ul><li>' . sprintf( __( 'Login to your WP Rocket %1$saccount%2$s', 'rocket' ), '<a href="https://wp-rocket.me/account/" rel="noopener noreferrer" target=_"blank">', '</a>' ) . '</li><li>' . __( 'Download the zip file', 'rocket' ) . '<li></li>' . __( 'Reinstall', 'rocket' ) . '</li></ul>' . sprintf( __( 'If the issue persists, please %1$scontact support%2$s.', 'rocket' ), '<a href="https://wp-rocket.me/support/" rel="noopener noreferrer" target=_"blank">', '</a>' ),
		];

		$rocket_options['secret_key'] = '';

		// Translators: %s = error message returned.
		set_transient( 'rocket_check_key_errors', [ sprintf( __( 'License validation failed: %s', 'rocket' ), $messages[ $json->data->reason ] ) ] );

		Logger::error(
			'License validation failed.',
			[
				'license validation process',
				'response_error' => $json->data->reason,
			]
		);

		set_transient( rocket_get_constant( 'WP_ROCKET_SLUG' ), $rocket_options );
		return $rocket_options;
	}

	$rocket_options['secret_key'] = $json->data->secret_key;

	if ( ! get_rocket_option( 'license' ) ) {
		$rocket_options['license'] = '1';
	}

	Logger::info( 'License validation successful.', [ 'license validation process' ] );

	set_transient( rocket_get_constant( 'WP_ROCKET_SLUG' ), $rocket_options );
	delete_transient( 'rocket_check_key_errors' );
	rocket_delete_licence_data_file();

	return $rocket_options;
}

/**
 * Deletes the licence-data.php file if it exists
 *
 * @since 3.5
 * @author Remy Perona
 *
 * @return void
 */
function rocket_delete_licence_data_file() {
	if ( is_multisite() ) {
		return;
	}

	$rocket_path = rocket_get_constant( 'WP_ROCKET_PATH' );

	if ( ! rocket_direct_filesystem()->exists( $rocket_path . 'licence-data.php' ) ) {
		return;
	}

	rocket_direct_filesystem()->delete( $rocket_path . 'licence-data.php' );
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
		$subfolder_install = ! $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = 1 AND meta_key = 'subdomain_install'" );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
		$root_url = rocket_get_home_url( '/' );
		return $root_url;
	}

	$current_network = get_network();

	if ( $current_network ) {
		$root_url = set_url_scheme( 'https://' . $current_network->domain . $current_network->path );
		$root_url = trailingslashit( $root_url );
	} else {
		$root_url = rocket_get_home_url( '/' );
	}

	return $root_url;
}
