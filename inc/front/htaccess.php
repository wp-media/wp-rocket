<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Used to flush the .htaccess file
 *
 * @since 1.1.0 Remove empty spacings when .htaccess is generated
 * @since 1.0
 *
 */

function flush_rocket_htaccess( $force = false )
{

	if ( ! $GLOBALS['is_apache'] ) {
		return false;
	}

	$rules = '';
	$htaccess_file =  get_home_path() . '.htaccess';

	if ( is_writable( $htaccess_file ) ) {

		// Get content of .htaccess file
		$ftmp = file_get_contents( $htaccess_file );

		// Remove the WP Rocket marker
		$ftmp = preg_replace( '/# BEGIN WP Rocket(.*)# END WP Rocket/isUe', '', $ftmp );

		// Remove empty spacings
		$ftmp = str_replace( "\n\n" , "\n" , $ftmp );

		if ( $force === false ) {
			$rules = get_rocket_htaccess_marker();
		}

		// Update the .htacces file
		rocket_put_content( $htaccess_file, $rules . $ftmp );

	}

}



/**
 * Return the markers for htacces rules
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_marker()
{
	
	// Recreate WP Rocket marker
	$marker  = '# BEGIN WP Rocket v' . WP_ROCKET_VERSION ."\n";
	$marker .= get_rocket_htaccess_charset();
	$marker .= get_rocket_htaccess_etag();
	$marker .= get_rocket_htaccess_files_match();
	$marker .= get_rocket_htaccess_mod_expires();
	$marker .= get_rocket_htaccess_mod_deflate();
	$marker .= get_rocket_htaccess_mod_rewrite();
	$marker .= '# END WP Rocket' . "\n";
	
	/**
	 * Filter rules added by WP Rocket in .htaccess
	 *
	 * @since 2.1
	 *
	 * @param string $marker The content of all rules 
	*/
	$marker = apply_filters( 'rocket_htaccess_marker', $marker );
	
	return $marker;

}



/**
 * Add somes rules need by the plugin
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_mod_rewrite()
{

	// No rewrite rules for multisite
	if ( is_multisite() ) {
		return;
	}

	// Get root base
	$home_root = parse_url( home_url() );
	$home_root = isset( $home_root['path'] ) ? trailingslashit($home_root['path']) : '/';

	$site_root = parse_url( site_url() );
	$site_root = isset( $site_root['path'] ) ? trailingslashit($site_root['path']) : '';

	// Get cache root
	if ( strpos( ABSPATH, WP_ROCKET_CACHE_PATH ) === false ) {
		$cache_root = str_replace( $_SERVER['DOCUMENT_ROOT'] , '', WP_ROCKET_CACHE_PATH);
	} else {
		$cache_root = $site_root . str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH );
	}

	// Set correct HOST depending on hook (not multisite compatible!)
	$HTTP_HOST = apply_filters( 'rocket_url_no_dots', false ) ? rocket_remove_url_protocol( home_url() ) : '%{HTTP_HOST}';

	$is_1and1_or_force = apply_filters( 'rocket_force_full_path', strpos( $_SERVER['DOCUMENT_ROOT'], '/kunden/' ) === 0 );

	$rules  = '<IfModule mod_rewrite.c>' . "\n";
	$rules .= 'RewriteEngine On' . "\n";
	$rules .= 'RewriteBase ' . $home_root . "\n";
	$rules .= 'RewriteCond %{REQUEST_METHOD} GET' . "\n";
	$rules .= 'RewriteCond %{QUERY_STRING} =""' . "\n";

	if ( $cookies = get_rocket_cache_reject_cookies() ) {
		$rules .= 'RewriteCond %{HTTP:Cookie} !(' . $cookies . ') [NC]' . "\n";
	}

	if ( $uri = get_rocket_cache_reject_uri() ) {
		$rules .= 'RewriteCond %{REQUEST_URI} !^(' . $uri . ')$ [NC]' . "\n";
	}

	$rules .= !is_rocket_cache_mobile() ? get_rocket_htaccess_mobile_rewritecond() : '';
	$rules .= !is_rocket_cache_ssl() ? get_rocket_htaccess_ssl_rewritecond() : '';

	if ( $is_1and1_or_force ) {

		$rules .= 'RewriteCond "' . str_replace( '/kunden/', '/', WP_ROCKET_CACHE_PATH ) . $HTTP_HOST . '%{REQUEST_URI}/index.html" -f' . "\n";

	} else  {

		$rules .= 'RewriteCond "%{DOCUMENT_ROOT}/' . ltrim( $cache_root, '/' ) . $HTTP_HOST . '%{REQUEST_URI}/index.html" -f' . "\n";

	}

	$rules .= 'RewriteRule .* "' . $cache_root . $HTTP_HOST . '%{REQUEST_URI}/index.html" [L]' . "\n";
	$rules .= '</IfModule>' . "\n";
	$rules = apply_filters( 'rocket_htaccess_mod_rewrite', $rules );

	return $rules;

}



/**
 * Other rules for mobile version
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_mobile_rewritecond()
{

	// No rewrite rules for multisite
	if ( is_multisite() ) {
		return;
	}

	$rules = 'RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).* [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).* [NC]' . "\n";
	$rules = apply_filters( 'rocket_htaccess_mobile_rewritecond', $rules );

	return $rules;

}



/**
 * Other rules for SSL requests
 *
 * @since 2.0
 *
 */

function get_rocket_htaccess_ssl_rewritecond()
{

	$rules = 'RewriteCond %{HTTPS} off' . "\n";
	$rules = apply_filters( 'rocket_htaccess_ssl_rewritecond', $rules );

	return $rules;

}



/**
 * Other rules again to improve performances
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_mod_deflate()
{

	$rules = '# Gzip compression' . "\n";
	$rules .= '<IfModule mod_deflate.c>' . "\n";
		$rules .= '# Active compression' . "\n";
		$rules .= 'SetOutputFilter DEFLATE' . "\n";
		$rules .= '# Force deflate for mangled headers developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping/' . "\n";
		$rules .= '<IfModule mod_setenvif.c>' . "\n";
			$rules .= '<IfModule mod_headers.c>' . "\n";
			$rules .= 'SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding' . "\n";
			$rules .= 'RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding' . "\n";
			$rules .= '</IfModule>' . "\n";
		$rules .= '</IfModule>' . "\n\n";
		$rules .= '# Compress all output labeled with one of the following MIME-types' . "\n";
		$rules .= '<IfModule mod_filter.c>' . "\n";
		$rules .= 'AddOutputFilterByType DEFLATE application/atom+xml \
		                          application/javascript \
		                          application/json \
		                          application/rss+xml \
		                          application/vnd.ms-fontobject \
		                          application/x-font-ttf \
		                          application/xhtml+xml \
		                          application/xml \
		                          font/opentype \
		                          image/svg+xml \
		                          image/x-icon \
		                          text/css \
		                          text/html \
		                          text/plain \
		                          text/x-component \
		                          text/xml' . "\n";
		$rules .= '</IfModule>' . "\n";
		$rules .= '<IfModule mod_headers.c>' . "\n";
             $rules .= 'Header append Vary User-Agent env=!dont-vary' . "\n";
       $rules .= '</IfModule>' . "\n";
	$rules .= '</IfModule>' . "\n\n";
	$rules = apply_filters( 'rocket_htaccess_mod_deflate', $rules );

	return $rules;

}



/**
 * Other rules to improve performances again
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_mod_expires()
{

	$rules = '# Expires headers (for better cache control)' . "\n";
	$rules .= '<IfModule mod_expires.c>' . "\n";
	  $rules .= 'ExpiresActive on' . "\n\n";
	  $rules .= '# Perhaps better to whitelist expires rules? Perhaps.' . "\n";
	  $rules .= 'ExpiresDefault                          "access plus 1 month"' . "\n\n";
	  $rules .= '# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)' . "\n";
	  $rules .= 'ExpiresByType text/cache-manifest       "access plus 0 seconds"' . "\n\n";
	  $rules .= '# Your document html' . "\n";
	  $rules .= 'ExpiresByType text/html                 "access plus 0 seconds"' . "\n\n";
	  $rules .= '# Data' . "\n";
	  $rules .= 'ExpiresByType text/xml                  "access plus 0 seconds"' . "\n";
	  $rules .= 'ExpiresByType application/xml           "access plus 0 seconds"' . "\n";
	  $rules .= 'ExpiresByType application/json          "access plus 0 seconds"' . "\n\n";
	  $rules .= '# Feed' . "\n";
	  $rules .= 'ExpiresByType application/rss+xml       "access plus 1 hour"' . "\n";
	  $rules .= 'ExpiresByType application/atom+xml      "access plus 1 hour"' . "\n\n";
	  $rules .= '# Favicon (cannot be renamed)' . "\n";
	  $rules .= 'ExpiresByType image/x-icon              "access plus 1 week"' . "\n\n";
	  $rules .= '# Media: images, video, audio' . "\n";
	  $rules .= 'ExpiresByType image/gif                 "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType image/png                 "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType image/jpeg                "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType video/ogg                 "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType audio/ogg                 "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType video/mp4                 "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType video/webm                "access plus 1 month"' . "\n\n";
	  $rules .= '# HTC files  (css3pie)' . "\n";
	  $rules .= 'ExpiresByType text/x-component          "access plus 1 month"' . "\n\n";
	  $rules .= '# Webfonts' . "\n";
	  $rules .= 'ExpiresByType application/x-font-ttf    "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType font/opentype             "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType application/x-font-woff   "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType image/svg+xml             "access plus 1 month"' . "\n";
	  $rules .= 'ExpiresByType application/vnd.ms-fontobject "access plus 1 month"' . "\n\n";
	  $rules .= '# CSS and JavaScript' . "\n";
	  $rules .= 'ExpiresByType text/css                  "access plus 1 year"' . "\n";
	  $rules .= 'ExpiresByType application/javascript    "access plus 1 year"'  . "\n\n";
	$rules .= '</IfModule>' . "\n\n";
	$rules = apply_filters( 'rocket_htaccess_mod_expires', $rules );

	return $rules;

}



/**
 * Add default charset
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_charset()
{

	// Get charset of the blog
	$charset = preg_replace( '/[^a-zA-Z0-9_\-\.:]+/', '', get_bloginfo( 'charset', 'display' ) );

	$rules = "# Use $charset encoding for anything served text/plain or text/html\n";
	$rules .= "AddDefaultCharset $charset\n";
	$rules .= "# Force $charset for a number of file formats\n";
	$rules .= "<IfModule mod_mime.c>\n";
		$rules .= "AddCharset $charset .atom .css .js .json .rss .vtt .xml\n";
	$rules .= "</IfModule>\n\n";
	$rules = apply_filters( 'rocket_htaccess_charset', $rules );

	return $rules;
}



/**
 * Add some files match rules
 *
 * @since 1.1.6
 *
 */

function get_rocket_htaccess_files_match()
{

	$rules = '<IfModule mod_alias.c>' . "\n";
		$rules .= '<FilesMatch "\.(html|htm|rtf|rtx|svg|svgz|txt|xsd|xsl|xml)$">' . "\n";
		    $rules .= '<IfModule mod_headers.c>' . "\n";
		         $rules .= 'Header set X-Powered-By "WP Rocket/' . WP_ROCKET_VERSION . '"' . "\n";
		         $rules .= 'Header unset Pragma' . "\n";
		         $rules .= 'Header append Cache-Control "public"' . "\n";
		         $rules .= 'Header unset Last-Modified' . "\n";
		    $rules .= '</IfModule>' . "\n";
		$rules .= '</FilesMatch>' . "\n\n";
		$rules .= '<FilesMatch "\.(css|htc|js|asf|asx|wax|wmv|wmx|avi|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|mpp|otf|odb|odc|odf|odg|odp|ods|odt|ogg|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|wav|wma|wri|xla|xls|xlsx|xlt|xlw|zip)$">' . "\n";
		    $rules .= '<IfModule mod_headers.c>' . "\n";
		        $rules .= 'Header unset Pragma' . "\n";
		        $rules .= 'Header append Cache-Control "public"' . "\n";
		    $rules .= '</IfModule>' . "\n";
		$rules .= '</FilesMatch>' . "\n";
	$rules .= '</IfModule>' . "\n\n";
	$rules = apply_filters( 'rocket_htaccess_files_match', $rules );

	return $rules;

}



/**
 * Rules to remove the etag
 *
 * @since 1.0
 *
 */

function get_rocket_htaccess_etag()
{

	$rules = "# FileETag None is not enough for every server.\n";
    $rules .= "<IfModule mod_headers.c>\n";
    $rules .= "Header unset ETag\n";
    $rules .= "</IfModule>\n\n";
    $rules .= "# Since we're sending far-future expires, we don't need ETags for\n";
    $rules .= "# static content.\n";
    $rules .= "# developer.yahoo.com/performance/rules.html#etags\n";
    $rules .= "FileETag None\n\n";
    $rules = apply_filters( 'rocket_htaccess_etag', $rules );

	return $rules;

}