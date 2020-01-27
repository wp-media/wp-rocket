<?php

defined( 'ABSPATH' ) || exit;

// Don't cache robots.txt && .htaccess directory (it's happened sometimes with weird server configuration).
if ( isset( $_SERVER['REQUEST_URI'] ) && ( strstr( wp_unslash( $_SERVER['REQUEST_URI'] ), 'robots.txt' ) || strstr( wp_unslash( $_SERVER['REQUEST_URI'] ), '.htaccess' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	rocket_define_donotoptimize_constant( true );

	return;
}

$rocket_request_uri = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
$rocket_request_uri = reset( $rocket_request_uri );

// Don't cache disallowed extensions.
if ( strtolower( wp_unslash( $_SERVER['REQUEST_URI'] ) ) !== '/index.php' && in_array( pathinfo( $rocket_request_uri, PATHINFO_EXTENSION ), [ 'php', 'xml', 'xsl' ], true ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache if user is in admin.
if ( is_admin() ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache ajax requests.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache the customizer preview.
if ( isset( $_POST['wp_customize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache without GET method.
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Get the correct config file.
$rocket_config_path      = WP_CONTENT_DIR . '/wp-rocket-config/';
$rocket_real_config_path = realpath( $rocket_config_path ) . DIRECTORY_SEPARATOR;

$rocket_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : (string) time(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
$rocket_host = preg_replace( '/:\d+$/', '', $rocket_host );
$rocket_host = trim( strtolower( $rocket_host ), '.' );
$rocket_host = rawurlencode( $rocket_host );

$rocket_continue = false;
if ( realpath( $rocket_config_path . $rocket_host . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $rocket_host . '.php' ), $rocket_real_config_path ) ) {
	include $rocket_config_path . $rocket_host . '.php';
	$rocket_continue = true;
} else {
	$rocket_path = str_replace( '\\', '/', strtok( $_SERVER['REQUEST_URI'], '?' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$rocket_path = preg_replace( '|(?<=.)/+|', '/', $rocket_path );
	$rocket_path = explode( '%2F', preg_replace( '/^(?:%2F)*(.*?)(?:%2F)*$/', '$1', rawurlencode( $rocket_path ) ) );

	foreach ( $rocket_path as $p ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		static $dir;

		if ( realpath( $rocket_config_path . $rocket_host . '.' . $p . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $rocket_host . '.' . $p . '.php' ), $rocket_real_config_path ) ) {
			include $rocket_config_path . $rocket_host . '.' . $p . '.php';
			$rocket_continue = true;
			break;
		}

		if ( realpath( $rocket_config_path . $rocket_host . '.' . $dir . $p . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $rocket_host . '.' . $dir . $p . '.php' ), $rocket_real_config_path ) ) {
			include $rocket_config_path . $rocket_host . '.' . $dir . $p . '.php';
			$rocket_continue = true;
			break;
		}

		$dir .= $p . '.'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	}
}

// Exit if no config file exists.
if ( ! $rocket_continue ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

/**
 * Don't cache with query strings parameters but the cache is served if the visitor comes from an RSS feed, a Facebook action or Google Adsense tracking
 *
 * @since 2.3 Add query strings which can be cached via the options page.
 * @since 2.1 Add compatibility with WordPress Landing Pages (permalink_name and lp-variation-id)
 * @since 2.1 Add compabitiliy with qTranslate and translation plugin with query string "lang"
 */
$rocket_remove_query_strings = [
	'utm_source'      => 1,
	'utm_medium'      => 1,
	'utm_campaign'    => 1,
	'utm_expid'       => 1,
	'fb_action_ids'   => 1,
	'fb_action_types' => 1,
	'fb_source'       => 1,
	'fbclid'          => 1,
	'gclid'           => 1,
	'age-verified'    => 1,
	'ao_noptimize'    => 1,
	'usqp'            => 1,
	'cn-reloaded'     => 1,
	'_ga'             => 1,
];

$rocket_params = [];

if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$rocket_params = array_diff_key( $_GET, $rocket_remove_query_strings ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! empty( $rocket_params ) ) {
		ksort( $rocket_params );

		$rocket_request_uri .= http_build_query( $rocket_params );
	}
}

$rocket_ignore_query_strings = [
	'lang'            => 1,
	's'               => 1,
	'permalink_name'  => 1,
	'lp-variation-id' => 1,
];

if ( ! empty( $rocket_params )
	&& ( ! (bool) array_intersect_key( $rocket_params, $rocket_ignore_query_strings ) )
	&& ( ! isset( $rocket_cache_query_strings ) || ! array_intersect( array_keys( $rocket_params ), $rocket_cache_query_strings ) )
) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache SSL.
if ( empty( $rocket_cache_ssl ) && is_ssl() ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache these pages.
if ( isset( $rocket_cache_reject_uri ) && preg_match( '#^(' . $rocket_cache_reject_uri . ')$#', $rocket_request_uri ) ) {
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache page with these cookies.
if ( isset( $rocket_cache_reject_cookies ) && preg_match( '#(' . $rocket_cache_reject_cookies . ')#', var_export( $_COOKIE, true ) ) ) { // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
	rocket_define_donotoptimize_constant( true );

	return;
}

$rocket_ip          = rocket_get_ip();
$rocket_allowed_ips = [
	'208.70.247.157' => '', // GT Metrix - Vancouver 1.
	'204.187.14.70'  => '', // GT Metrix - Vancouver 2.
	'204.187.14.71'  => '', // GT Metrix - Vancouver 3.
	'204.187.14.72'  => '', // GT Metrix - Vancouver 4.
	'204.187.14.73'  => '', // GT Metrix - Vancouver 5.
	'204.187.14.74'  => '', // GT Metrix - Vancouver 6.
	'204.187.14.75'  => '', // GT Metrix - Vancouver 7.
	'204.187.14.76'  => '', // GT Metrix - Vancouver 8.
	'204.187.14.77'  => '', // GT Metrix - Vancouver 9.
	'204.187.14.78'  => '', // GT Metrix - Vancouver 10.
	'199.10.31.194'  => '', // GT Metrix - Vancouver 11.
	'13.85.80.124'   => '', // GT Metrix - Dallas 1.
	'13.84.146.132'  => '', // GT Metrix - Dallas 2.
	'13.84.146.226'  => '', // GT Metrix - Dallas 3.
	'40.74.254.217'  => '', // GT Metrix - Dallas 4.
	'13.84.43.227'   => '', // GT Metrix - Dallas 5.
	'172.255.61.34'  => '', // GT Metrix - London 1.
	'172.255.61.35'  => '', // GT Metrix - London 2.
	'172.255.61.36'  => '', // GT Metrix - London 3.
	'172.255.61.37'  => '', // GT Metrix - London 4.
	'172.255.61.38'  => '', // GT Metrix - London 5.
	'172.255.61.39'  => '', // GT Metrix - London 6.
	'172.255.61.40'  => '', // GT Metrix - London 7.
	'13.70.66.20'    => '', // GT Metrix - Sydney.
	'191.235.85.154' => '', // GT Metrix - São Paulo 1.
	'191.235.86.0'   => '', // GT Metrix - São Paulo 2.
	'52.66.75.147'   => '', // GT Metrix - Mumbai.
	'52.175.28.116'  => '', // GT Metrix - Hong Kong.
];

// Don't cache page when these cookies don't exist.
if ( ( ! isset( $rocket_allowed_ips[ $rocket_ip ] ) && ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) && ! preg_match( '#(PingdomPageSpeed|DareBoost|Google|PTST|WP Rocket)#i', $_SERVER['HTTP_USER_AGENT'] ) ) && isset( $rocket_cache_mandatory_cookies ) && ! preg_match( '#(' . $rocket_cache_mandatory_cookies . ')#', var_export( $_COOKIE, true ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.PHP.DevelopmentFunctions.error_log_var_export
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache page with these user agents.
if ( isset( $rocket_cache_reject_ua, $_SERVER['HTTP_USER_AGENT'] ) && ! empty( $rocket_cache_reject_ua ) && preg_match( '#(' . $rocket_cache_reject_ua . ')#', $_SERVER['HTTP_USER_AGENT'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	rocket_define_donotoptimize_constant( true );

	return;
}

// Don't cache if mobile detection is activated.
if ( ! isset( $rocket_cache_mobile ) && isset( $_SERVER['HTTP_USER_AGENT'] ) && ( preg_match( '#^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).*#i', $_SERVER['HTTP_USER_AGENT'] ) || preg_match( '#^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).*#i', substr( $_SERVER['HTTP_USER_AGENT'], 0, 4 ) ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	rocket_define_donotoptimize_constant( true );

	return;
}

// Check if dots should be replace by underscores.
$rocket_host = isset( $rocket_url_no_dots ) ? str_replace( '.', '_', $rocket_host ) : $rocket_host;

// Get cache folder of host name.
if ( isset( $rocket_cookie_hash )
	&& isset( $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ] )
	&& isset( $rocket_cache_reject_cookies )
	&& ! strstr( $rocket_cache_reject_cookies, 'wordpress_logged_in_' )
) {
	if ( isset( $rocket_common_cache_logged_users ) ) {
		$request_uri_path = $rocket_cache_path . $rocket_host . '-loggedin' . rtrim( $rocket_request_uri, '/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	} else {
		$rocket_user_key = explode( '|', $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$rocket_user_key = reset( ( $rocket_user_key ) );
		$rocket_user_key = $rocket_user_key . '-' . $rocket_secret_cache_key;

		// Get cache folder of host name.
		$request_uri_path = $rocket_cache_path . $rocket_host . '-' . $rocket_user_key . rtrim( $rocket_request_uri, '/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	}
}
else {
	$request_uri_path = $rocket_cache_path . $rocket_host . rtrim( $rocket_request_uri, '/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
}

$rocket_filename = 'index';

// Rename the caching filename for mobile.
if ( isset( $rocket_cache_mobile, $rocket_do_caching_mobile_files, $rocket_cache_mobile_files_tablet ) && class_exists( 'Rocket_Mobile_Detect' ) ) {
	$rocket_detect = new Rocket_Mobile_Detect();

	if ( $rocket_detect->isMobile() && ! $rocket_detect->isTablet() && 'desktop' === $rocket_cache_mobile_files_tablet || ( $rocket_detect->isMobile() || $rocket_detect->isTablet() ) && 'mobile' === $rocket_cache_mobile_files_tablet ) {
		$rocket_filename .= '-mobile';
	}
}

// Rename the caching filename for SSL URLs.
if ( ( is_ssl() && ! empty( $rocket_cache_ssl ) ) ) {
	$rocket_filename .= '-https';
}

// Rename the caching filename depending to dynamic cookies.
if ( ! empty( $rocket_cache_dynamic_cookies ) ) {
	foreach ( $rocket_cache_dynamic_cookies as $key => $cookie_name ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		if ( is_array( $cookie_name ) && isset( $_COOKIE[ $key ] ) ) {
			foreach ( $cookie_name as $cookie_key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				if ( isset( $_COOKIE[ $key ][ $cookie_key ] ) && '' !== $_COOKIE[ $key ][ $cookie_key ] ) {
					$rocket_cache_key = $_COOKIE[ $key ][ $cookie_key ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					$rocket_cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
					$rocket_filename .= '-' . $rocket_cache_key;
				}
			}
			continue;
		}

		if ( isset( $_COOKIE[ $cookie_name ] ) && '' !== $_COOKIE[ $cookie_name ] ) {
			$rocket_cache_key = $_COOKIE[ $cookie_name ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$rocket_cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
			$rocket_filename .= '-' . $rocket_cache_key;
		}
	}
}

// Caching file path.
$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', 'rocket_urlencode_lowercase', $request_uri_path ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Directories in Windows can't contain question marks.
$request_uri_path = str_replace( '?', '_', $request_uri_path ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$rocket_cache_filepath = $request_uri_path . '/' . $filename . '.html';


// Serve the cache file if exist.
rocket_serve_cache_file( $rocket_cache_filepath );

ob_start( 'do_rocket_callback' );

/**
 * The famous callback, it puts contents in a cache file if buffer length > 255 (IE do not read pages under 255 c. )
 *
 * @since 1.3.0 Add filter rocket_buffer
 * @since 1.0
 *
 * @param string $buffer The buffer content.
 * @return string the buffered content
 */
function do_rocket_callback( $buffer ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	/**
	 * Allow to cache search results
	 *
	 * @since 2.3.8
	 *
	 * @param bool true will force caching search results.
	 */
	$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

	/**
	 * Allow to override the DONOTCACHEPAGE behavior.
	 * To warn conflict with some plugins like Thrive Leads.
	 *
	 * @since 2.5
	 *
	 * @param bool true will force the override.
	 */
	$rocket_override_donotcachepage = apply_filters( 'rocket_override_donotcachepage', false );

	if ( strlen( $buffer ) > 255
		&& ( http_response_code() === 200 ) // only cache 200.
		&& ( function_exists( 'is_404' ) && ! is_404() ) // Don't cache 404.
		&& ( function_exists( 'is_search' ) && ! is_search() || $rocket_cache_search ) // Don't cache search results.
		&& ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE || $rocket_override_donotcachepage ) // Don't cache template that use this constant.
		&& function_exists( 'rocket_mkdir_p' )
	) {
		global $request_uri_path, $rocket_cache_filepath, $is_nginx;

		$footprint = '';
		$is_html   = false;

		if ( preg_match( '/(<\/html>)/i', $buffer ) ) {
			$is_html = true;
		}

		/**
		 * Allow to the generate the caching file
		 *
		 * @since 2.5
		 *
		 * @param bool true will force the caching file generation.
		 */
		if ( apply_filters( 'do_rocket_generate_caching_files', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			// Create cache folders of the request uri.
			rocket_mkdir_p( $request_uri_path );

			if ( $is_html ) {
				$footprint = get_rocket_footprint();
			}

			// Save the cache file.
			rocket_put_content( $rocket_cache_filepath, $buffer . $footprint );

			if ( get_rocket_option( 'do_caching_mobile_files' ) ) {
				if ( $is_nginx ) {
					// Create a hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
					$nginx_mobile_detect_file = $request_uri_path . '/.mobile-active';

					if ( ! rocket_direct_filesystem()->exists( $nginx_mobile_detect_file ) ) {
						rocket_direct_filesystem()->touch( $nginx_mobile_detect_file );
					}
				}
			}

			if ( function_exists( 'gzencode' ) ) {
				rocket_put_content( $rocket_cache_filepath . '_gzip', gzencode( $buffer . $footprint, apply_filters( 'rocket_gzencode_level_compression', 3 ) ) );
			}

			// Send headers with the last modified time of the cache file.
			if ( file_exists( $rocket_cache_filepath ) ) {
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );
			}
		}

		if ( $is_html ) {
			$footprint = get_rocket_footprint( false );
		}

		$buffer = $buffer . $footprint;
	}

	return $buffer;
}

/**
 * Serve the cache file if exist
 *
 * @since 2.11 Serve the gzipped cache file if possible
 * @since 2.0
 *
 * @param string $rocket_cache_filepath Path to the cache file.
 */
function rocket_serve_cache_file( $rocket_cache_filepath ) {
	$rocket_cache_filepath_gzip = $rocket_cache_filepath . '_gzip';

	// Check if cache file exist.
	if ( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) && file_exists( $rocket_cache_filepath_gzip ) && is_readable( $rocket_cache_filepath_gzip ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath_gzip ) ) . ' GMT' );

		// Getting If-Modified-Since headers sent by the client.
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers                = apache_request_headers();
			$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
		} else {
			$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		}

		// Checking if the client is validating his cache and if it is current.
		if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath_gzip ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : '' ) . ' 304 Not Modified', true, 304 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-cache, must-revalidate' );

			exit;
		}

		// Serve the cache if file isn't store in the client browser cache.
		readgzfile( $rocket_cache_filepath_gzip );

		exit;
	}

	if ( file_exists( $rocket_cache_filepath ) && is_readable( $rocket_cache_filepath ) ) {
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );

		// Getting If-Modified-Since headers sent by the client.
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers                = apache_request_headers();
			$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
		} else {
			$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		}

		// Checking if the client is validating his cache and if it is current.
		if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : '' ) . ' 304 Not Modified', true, 304 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-cache, must-revalidate' );

			exit;
		}

		// Serve the cache if file isn't store in the client browser cache.
		readfile( $rocket_cache_filepath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile

		exit;
	}
}

/**
 * Declares and sets value of constant preventing Optimizations
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param bool $value true or false.
 */
function rocket_define_donotoptimize_constant( $value ) {
	if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
		define( 'DONOTROCKETOPTIMIZE', (bool) $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
	}
}

/**
 * Force lowercase on encoded url strings from different alphabets to prevent issues on some hostings
 *
 * @since 2.7
 *
 * @param string $matches Cache path.
 * @return string cache path in lowercase
 */
function rocket_urlencode_lowercase( $matches ) {
	return strtolower( $matches[0] );
}

/**
 * Get the IP address from which the user is viewing the current page.
 *
 * @since 2.7.3
 */
function rocket_get_ip() {
	$keys = [
		'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_X_REAL_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	];

	foreach ( $keys as $key ) {
		if ( array_key_exists( $key, $_SERVER ) ) {
			$ip = explode( ',', $_SERVER[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$ip = end( $ip );

			if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}

	return '0.0.0.0';
}
