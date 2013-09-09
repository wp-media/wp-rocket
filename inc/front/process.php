<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Catch the pages contents if needed, then, starts an Output Buffer (ob_start) with a callback
 *
 * since 1.0
 *
 */

add_action( 'template_redirect', 'ob_rocket_callback', -1 );
function ob_rocket_callback()
{

	// Don't cache without GET method 		// Don't cache with variables 	// Don't cache 404   // Don't cache SSL
	if( $_SERVER['REQUEST_METHOD'] != 'GET'	|| !empty($_GET) 				|| is_404() ||       is_ssl() ||
	// Don't cache page with this cookie
	preg_match('#(' . get_rocket_cookies_not_cached() . ')#', var_export($_COOKIE, true)) ||
	// Don't cache this pages
	preg_match('#^(' . get_rocket_pages_not_cached() . ')$#', $_SERVER['REQUEST_URI']) ||
	// Don't cache not allowed extensions
	in_array( pathinfo( $_SERVER['REQUEST_URI'], PATHINFO_EXTENSION ), array( 'txt', 'xml' ) ) ||
	// Don't cache if mobile detection is activated
	!is_rocket_cache_mobile() && (preg_match('#^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).*#i', $_SERVER['HTTP_USER_AGENT']) || preg_match('#^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).*#i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4)))
	)
		return;
	else
		ob_start( 'do_rocket_process' );
}



/**
 * The famous callback, it puts contents in a cache file if buffer length > 255 (IE do not read pages under 255 c. )
 *
 * @since 1.3.0 Add filter rocket_buffer
 * @since 1.0
 *
 */

function do_rocket_process( $buffer )
{

	if( strlen( $buffer ) > 255 ) {
		
		// This hook is used for :
		// - Add width and height attributes on images
		// - Deferred JavaScript files
		// - Minification HTML/CSS/JavaScript
		$buffer = apply_filters( 'rocket_buffer', $buffer );
			    
		// Create cache folder of host name
	    $request_uri_path = WP_ROCKET_CACHE_PATH . $_SERVER['HTTP_HOST'] . rtrim( $_SERVER['REQUEST_URI'], '/' );
		
		// Get path of cache file
		$cache_file_path = $request_uri_path . '/index.html';
		
		// Create cache folders of the requet uri
		rocket_mkdir_p( $request_uri_path );
		
		// Save the cache file
	    file_put_contents( $cache_file_path, $buffer . "\n" . '<!-- This website is like a Rocket, isn\'t ? Performance optimized by WP Rocket. Learn more: http://wp-rocket.me - Debug: cached@'.time().'-->' );
    }

	return $buffer . "\n" . '<!-- This website is like a Rocket, isn\'t ? Performance optimized by WP Rocket. Learn more: http://wp-rocket.me -->';
}