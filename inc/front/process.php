<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

// Don't cache WP javascript generators
if ( strstr( $_SERVER['SCRIPT_FILENAME'], 'wp-includes/js' ) ) {
	return;
}

// Don't cache robots.txt
if ( strstr( $_SERVER['REQUEST_URI'], 'robots.txt') ) {
	return;
}

// Don't cache not allowed extensions
if ( strtolower( $_SERVER['REQUEST_URI'] ) != '/index.php' && in_array( pathinfo( $_SERVER['REQUEST_URI'], PATHINFO_EXTENSION ), array( 'php', 'xml', 'xsl' ) ) ) {
	return;
}

// Don't cache if user is in admin
if ( is_admin() ) {
	return;
}

// Don't cache without GET method
if ( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
	return;
}

// Don't cache with variables
// but the cache is enabled if the visitor comes from an RSS feed or an Facebook action
// @since 2.1 Add compatibilty with WordPress Landing Pages (permalink_name and lp-variation-id)
// @since 2.1 Add compabitiliy with qTranslate and translation plugin with query string "lang"
if ( ! empty( $_GET )
	&& ( ! isset( $_GET['utm_source'], $_GET['utm_medium'], $_GET['utm_campaign'] ) )
	&& ( ! isset( $_GET['fb_action_ids'], $_GET['fb_action_types'], $_GET['fb_source'] ) )
	&& ( ! isset( $_GET['permalink_name'] ) )
	&& ( ! isset( $_GET['lp-variation-id'] ) )
	&& ( ! isset( $_GET['lang'] ) )
)
	return;

// Get the correct config file
$rocket_config_path = WP_CONTENT_DIR . '/wp-rocket-config/';
$host = trim( strtolower( $_SERVER['HTTP_HOST'] ), '.' );
$request_uri = isset( $_GET['lp-variation-id'] ) || isset( $_GET['lang'] ) ? $_SERVER['REQUEST_URI'] : reset(( explode( '?', $_SERVER['REQUEST_URI'] ) ));

$continue = false;
if ( file_exists( $rocket_config_path . $host . '.php' ) ) {
	include( $rocket_config_path . $host . '.php' );
	$continue = true;
} else {
	$path = explode( '/' , trim( $_SERVER['REQUEST_URI'], '/' ) );

	foreach ( $path as $p ) {		
		static $dir;

		if ( file_exists( $rocket_config_path . $host . '.' . $p . '.php' ) ) {
			include( $rocket_config_path . $host . '.' . $p .'.php' );
			$continue = true;
			break;
		}

		if( file_exists( $rocket_config_path . $host . '.' . $dir . $p . '.php' ) ) {
			include( $rocket_config_path . $host . '.' . $dir. $p . '.php' );
			$continue = true;
			break;
		}

		$dir .= $p . '.';
	}
}

// Exit if no config file is exist
if ( ! $continue ) {
	return;
}

// Don't cache SSL
if ( ! isset( $rocket_cache_ssl ) && rocket_is_ssl() ) {
	return;
}

// Don't cache this pages
if ( isset( $rocket_cache_reject_uri ) && preg_match( '#^(' . $rocket_cache_reject_uri . ')$#', $request_uri ) ) {
	return;
}

// Don't cache page with this cookie
if ( isset( $rocket_cache_reject_cookies ) && preg_match( '#(' . $rocket_cache_reject_cookies . ')#', var_export( $_COOKIE, true ) ) ) {
	return;
}

// Don't cache if mobile detection is activated
if ( ! isset( $rocket_cache_mobile ) && (preg_match('#^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).*#i', $_SERVER['HTTP_USER_AGENT']) || preg_match('#^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).*#i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4))) ) {
	return;
}

// Check if dots should be replace by underscores
$host = isset( $rocket_url_no_dots ) ? str_replace( '.', '_', $host ) : $host;

// Get cache folder of host name
if ( isset( $rocket_cookie_hash )
	&& isset( $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ] )
	&& isset( $rocket_cache_reject_cookies )
	&& !strstr( $rocket_cache_reject_cookies, 'wordpress_logged_in_' )
) {
	$user_key = reset( ( explode( '|', $_COOKIE[ 'wordpress_logged_in_' . $rocket_cookie_hash ]) ) ) . '-' . $rocket_secret_cache_key;

	// Get cache folder of host name
	$request_uri_path = $rocket_cache_path . $host . '-' . $user_key . rtrim( $request_uri, '/' );
}
else {
	$request_uri_path = $rocket_cache_path . $host . rtrim( $request_uri, '/' );
}

// Serve the cache file if exist
rocket_serve_cache_file( $request_uri_path );

ob_start( 'do_rocket_callback' );

/**
 * The famous callback, it puts contents in a cache file if buffer length > 255 (IE do not read pages under 255 c. )
 *
 * @since 1.3.0 Add filter rocket_buffer
 * @since 1.0
 */
function do_rocket_callback( $buffer )
{
	if ( strlen( $buffer ) > 255
		&& !is_404() 	// Don't cache 404
		&& !is_search() // Don't cache search results
		&& !defined( 'DONOTCACHEPAGE' ) || !DONOTCACHEPAGE // Don't cache template that use this constant
	) {
		global $request_uri_path;

		// This hook is used for :
		// - Add width and height attributes on images
		// - Deferred JavaScript files
		// - DNS Prefechting
		// - Minification HTML/CSS/JavaScript
		$buffer = apply_filters( 'rocket_buffer', $buffer );

		// Create cache folders of the request uri
		rocket_mkdir_p( $request_uri_path );

		// Save the cache file
		rocket_put_content( $request_uri_path . '/index.html', $buffer . get_rocket_footprint() );

		// Send headers with the last modified time of the cache file
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $request_uri_path . '/index.html' ) ) . ' GMT' );
	}

	return $buffer . get_rocket_footprint(false);
}

/**
 * Serve the cache file if exist
 *
 * @since 2.0
 */
function rocket_serve_cache_file( $request_uri_path )
{
	$filename = $request_uri_path . '/index.html';

	// Check if cache file exist
	if ( file_exists( $filename ) && is_readable( $filename ) ) {

		// Getting If-Modified-Since headers sent by the client.
		if ( function_exists( 'apache_request_headers' ) ) {			
			$headers = apache_request_headers();
			$http_if_modified_since = isset( $headers[ 'If-Modified-Since' ] ) ? $headers[ 'If-Modified-Since' ] : '';			
		} else {
			$http_if_modified_since = $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ];
		}

		// Checking if the client is validating his cache and if it is current.
	    if ( isset( $http_if_modified_since ) && ( strtotime( $http_if_modified_since ) == filemtime( $filename ) ) ) {        
	        // Client's cache is current, so we just respond '304 Not Modified'.
	        header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified' );
	        exit;	        
	    }

	   // Serve the cache if file isn't store in the client browser cache
	   readfile( $filename );
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
function rocket_is_ssl()
{
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) ) {
			return true;
		}
		if ( '1' == $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}