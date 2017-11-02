<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

// Don't cache robots.txt && .htaccess directory (it's happened sometimes with weird server configuration).
if ( strstr( $_SERVER['REQUEST_URI'], 'robots.txt' ) || strstr( $_SERVER['REQUEST_URI'], '.htaccess' ) ) {
	return;
}

$request_uri = explode( '?', $_SERVER['REQUEST_URI'] );
$request_uri = reset( ( $request_uri ) );

// Don't cache disallowed extensions.
if ( strtolower( $_SERVER['REQUEST_URI'] ) !== '/index.php' && in_array( pathinfo( $request_uri, PATHINFO_EXTENSION ), array( 'php', 'xml', 'xsl' ), true ) ) {
	return;
}

// Don't cache if user is in admin.
if ( is_admin() ) {
	return;
}

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	return;
}

// Don't cache the customizer preview.
if ( isset( $_POST['wp_customize'] ) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache without GET method.
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
		rocket_define_donotminify_constants( true );
		rocket_define_donotasync_css_constant( true );
		return;
}

// Get the correct config file.
$rocket_config_path = WP_CONTENT_DIR . '/wp-rocket-config/';
$real_rocket_config_path = realpath( $rocket_config_path ) . DIRECTORY_SEPARATOR;
$host = ( isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : time();
$host = trim( strtolower( $host ), '.' );
$host = urlencode( $host );

$continue = false;
if ( realpath( $rocket_config_path . $host . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $host . '.php' ), $real_rocket_config_path ) ) {
	include( $rocket_config_path . $host . '.php' );
	$continue = true;
} else {
	$path = str_replace( '\\', '/', strtok( $_SERVER['REQUEST_URI'], '?' ) );
	$path = preg_replace( '|(?<=.)/+|', '/', $path );
	$path = explode( '%2F' , trim( urlencode( $path ), '%2F' ) );

	foreach ( $path as $p ) {
		static $dir;

		if ( realpath( $rocket_config_path . $host . '.' . $p . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $host . '.' . $p . '.php' ), $real_rocket_config_path ) ) {
			include( $rocket_config_path . $host . '.' . $p . '.php' );
			$continue = true;
			break;
		}

		if ( realpath( $rocket_config_path . $host . '.' . $dir . $p . '.php' ) && 0 === stripos( realpath( $rocket_config_path . $host . '.' . $dir . $p . '.php' ), $real_rocket_config_path ) ) {
			include( $rocket_config_path . $host . '.' . $dir . $p . '.php' );
			$continue = true;
			break;
		}

		$dir .= $p . '.';
	}
}

// Exit if no config file exists.
if ( ! $continue ) {
	return;
}

$request_uri = ( isset( $rocket_cache_query_strings ) && array_intersect( array_keys( $_GET ), $rocket_cache_query_strings ) ) || isset( $_GET['lp-variation-id'] ) || isset( $_GET['lang'] ) || isset( $_GET['s'] ) ? $_SERVER['REQUEST_URI'] : $request_uri;

/**
 * Don't cache with variables but the cache is enabled if the visitor comes from an RSS feed, a Facebook action or Google Adsense tracking
 *
 * @since 2.3 Add query strings which can be cached via the options page.
 * @since 2.1 Add compatibilty with WordPress Landing Pages (permalink_name and lp-variation-id)
 * @since 2.1 Add compabitiliy with qTranslate and translation plugin with query string "lang"
 */
if ( ! empty( $_GET )
	&& ( ! isset( $_GET['utm_source'], $_GET['utm_medium'], $_GET['utm_campaign'] ) )
	&& ( ! isset( $_GET['utm_expid'] ) )
	&& ( ! isset( $_GET['fb_action_ids'], $_GET['fb_action_types'], $_GET['fb_source'] ) )
	&& ( ! isset( $_GET['gclid'] ) )
	&& ( ! isset( $_GET['permalink_name'] ) )
	&& ( ! isset( $_GET['lp-variation-id'] ) )
	&& ( ! isset( $_GET['lang'] ) )
	&& ( ! isset( $_GET['s'] ) )
	&& ( ! isset( $_GET['age-verified'] ) )
	&& ( ! isset( $_GET['ao_noptimize'] ) )
	&& ( ! isset( $_GET['usqp'] ) )
	&& ( ! isset( $rocket_cache_query_strings ) || ! array_intersect( array_keys( $_GET ), $rocket_cache_query_strings ) )
) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache SSL.
if ( ! isset( $rocket_cache_ssl ) && rocket_is_ssl() ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache these pages.
if ( isset( $rocket_cache_reject_uri ) && preg_match( '#^(' . $rocket_cache_reject_uri . ')$#', $request_uri ) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache page with these cookies.
if ( isset( $rocket_cache_reject_cookies ) && preg_match( '#(' . $rocket_cache_reject_cookies . ')#', var_export( $_COOKIE, true ) ) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

$ip	= rocket_get_ip();
$allowed_ips = array(
	'85.17.131.209'  => 0, // Pingdom Tools - Amsterdam.
	'173.208.58.138' => 1, // Pingdom Tools - New-York.
	'50.22.90.226'   => 2, // Pingdom Tools - Dallas.
	'209.58.131.213' => 3, // Pingdom Tools - San Jose.
	'168.1.92.52'    => 4, // Pingdom Tools - Melbourne.
	'5.178.78.78'    => 5,// Pingdom Tools - Stockholm.
);

// Don't cache page when these cookies don't exist.
if ( ! isset( $allowed_ips[ $ip ] ) && isset( $rocket_cache_mandatory_cookies ) && ! preg_match( '#(' . $rocket_cache_mandatory_cookies . ')#', var_export( $_COOKIE, true ) ) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache page with these user agents.
if ( isset( $rocket_cache_reject_ua, $_SERVER['HTTP_USER_AGENT'] ) && ! empty( $rocket_cache_reject_ua ) && preg_match( '#(' . $rocket_cache_reject_ua . ')#', $_SERVER['HTTP_USER_AGENT'] ) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Don't cache if mobile detection is activated.
if ( ! isset( $rocket_cache_mobile ) && isset( $_SERVER['HTTP_USER_AGENT'] ) && (preg_match( '#^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).*#i', $_SERVER['HTTP_USER_AGENT'] ) || preg_match( '#^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).*#i', substr( $_SERVER['HTTP_USER_AGENT'], 0, 4 ) )) ) {
	rocket_define_donotminify_constants( true );
	rocket_define_donotasync_css_constant( true );
	return;
}

// Check if dots should be replace by underscores.
$host = isset( $rocket_url_no_dots ) ? str_replace( '.', '_', $host ) : $host;

// Get cache folder of host name.
if ( isset( $rocket_cookie_hash )
	&& isset( $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ] )
	&& isset( $rocket_cache_reject_cookies )
	&& ! strstr( $rocket_cache_reject_cookies, 'wordpress_logged_in_' )
) {
	if ( isset( $rocket_common_cache_logged_users ) ) {
		$request_uri_path = $rocket_cache_path . $host . '-loggedin' . rtrim( $request_uri, '/' );
	} else {
		$user_key = explode( '|', $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ] );
		$user_key = reset( ( $user_key ) );
		$user_key = $user_key . '-' . $rocket_secret_cache_key;

		// Get cache folder of host name.
		$request_uri_path = $rocket_cache_path . $host . '-' . $user_key . rtrim( $request_uri, '/' );
	}
}
else {
	$request_uri_path = $rocket_cache_path . $host . rtrim( $request_uri, '/' );
}

$filename = 'index';

// Rename the caching filename for mobile.
if ( isset( $rocket_cache_mobile, $rocket_do_caching_mobile_files ) && class_exists( 'Rocket_Mobile_Detect' ) ) {
	$detect = new Rocket_Mobile_Detect();

	if ( $detect->isMobile() && ! $detect->isTablet() ) {
		$filename .= '-mobile';
	}
}

// Rename the caching filename for SSL URLs.
if ( ( rocket_is_ssl() && ! empty( $rocket_cache_ssl ) ) ) {
	$filename .= '-https';
}

// Rename the caching filename depending to dynamic cookies.
if ( ! empty( $rocket_cache_dynamic_cookies ) ) {
	foreach ( $rocket_cache_dynamic_cookies as $cookie_name ) {
		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$cache_key = $_COOKIE[ $cookie_name ];
			$cache_key = preg_replace( '/[^a-z0-9_\-]/i', '-', $cache_key );
			$filename .= '-' . $cache_key;
		}
	}
}

// Caching file path.
$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', 'rocket_urlencode_lowercase', $request_uri_path );
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
function do_rocket_callback( $buffer ) {
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
		&& ( function_exists( 'is_404' ) && ! is_404() ) // Don't cache 404
		&& ( function_exists( 'is_search' ) && ! is_search() || $rocket_cache_search ) // Don't cache search results
		&& ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE || $rocket_override_donotcachepage ) // Don't cache template that use this constant.
		&& function_exists( 'rocket_mkdir_p' )
	) {
		global $request_uri_path, $rocket_cache_filepath, $is_nginx;

		$footprint = '';
		$is_html   = false;

		if ( preg_match( '/(<\/html>)/i', $buffer ) ) {
			/**
			 * This hook is used for:
			 * - Add width and height attributes on images
			 * - Deferred JavaScript files
			 * - DNS Prefechting
			 * - Minification HTML/CSS/JavaScript
			 * - CDN
			 * - LazyLoad
			 */
			$buffer = apply_filters( 'rocket_buffer', $buffer );

			$is_html = true;
		}

		/**
		  * Allow to the generate the caching file
		  *
		  * @since 2.5
		  *
		  * @param bool true will force the caching file generation.
		 */
		if ( apply_filters( 'do_rocket_generate_caching_files', true ) ) {
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
 * @since 2.0
 *
 * @param string $rocket_cache_filepath Path to the cache file.
 */
function rocket_serve_cache_file( $rocket_cache_filepath ) {

	// Check if cache file exist.
	if ( file_exists( $rocket_cache_filepath ) && is_readable( $rocket_cache_filepath ) ) {

		// Getting If-Modified-Since headers sent by the client.
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
			$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
		} else {
			$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ?$_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
		}

		// Checking if the client is validating his cache and if it is current.
	    if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath ) ) ) {
	        // Client's cache is current, so we just respond '304 Not Modified'.
	        header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
	        exit;
	    }

		// Serve the cache if file isn't store in the client browser cache.
		readfile( $rocket_cache_filepath );
		exit;
	}
}

/**
 * Determine if SSL is used
 *
 * @since 2.0
 *
 * @source is_ssl() in /wp-includes/functions.php
 */
function rocket_is_ssl() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}
		if ( '1' === $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' === $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}

/**
 * Declare and set value to DONOTMINIFYCSS & DONOTMINIFYJS constant
 *
 * @since 2.6.2
 *
 * @param bool $value true or false.
 */
function rocket_define_donotminify_constants( $value ) {
	if ( ! defined( 'DONOTMINIFYCSS' ) ) {
		define( 'DONOTMINIFYCSS', (bool) $value );
	}

	if ( ! defined( 'DONOTMINIFYJS' ) ) {
		define( 'DONOTMINIFYJS', (bool) $value );
	}
}

/**
 * Declare and set value to DONOTMASYNCCSS constant
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @param bool $value true or false.
 */
function rocket_define_donotasync_css_constant( $value ) {
	if ( ! defined( 'DONOTASYNCCSS' ) ) {
		define( 'DONOTASYNCCSS', (bool) $value );
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
	$keys = array(
		'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_X_REAL_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	);

	foreach ( $keys as $key ) {
		if ( array_key_exists( $key, $_SERVER ) ) {
			$ip = explode( ',', $_SERVER[ $key ] );
			$ip = end( $ip );

			if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}

	return '0.0.0.0';
}
