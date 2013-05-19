<?php


/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function flush_rocket_htaccess( $force = false )
{

	$file = '';
	$htaccess_file = ABSPATH . '.htaccess';

	if( file_exists( $htaccess_file ) && is_writeable( $htaccess_file ) )
	{
		
		// Get content of .htaccess file
		$ftmp = file_get_contents( $htaccess_file );
		
		// Delete the WP Rocket marker
		$ftmp = preg_replace( '/# BEGIN WP Rocket(.*)# END WP Rocket/isUe', '', $ftmp );
		
		
		if( $force === false  ) {
			
			// Recreate WP Rocket marker
			$file  = '# BEGIN WP Rocket' . "\n";
			$file .= get_rocket_htaccess_charset();
			$file .= get_rocket_htaccess_etag();
			$file .= get_rocket_htaccess_expires();
			$file .= get_rocket_htaccess_mod_deflate();
			$file .= get_rocket_htaccess_mod_rewrite();
			$file .= '# END WP Rocket'. "\n\n";
				
		}
		

		// Update the .htacces file
		file_put_contents( $htaccess_file , $file . $ftmp );

	}
	else
	{
		// TO DO - Message d'erreur
	}

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function get_rocket_htaccess_mod_rewrite()
{

	// Get root base
	$home_root = parse_url(home_url());
	$home_root = isset( $home_root['path'] ) ? trailingslashit($home_root['path']) : '/';

	// Get cache root
	$cache_root = str_replace( site_url( '/' ), '', WP_ROCKET_CACHE_URL );

	$rules  = '<IfModule mod_rewrite.c>' . "\n";
	$rules .= 'RewriteEngine On' . "\n";
	$rules .= 'RewriteBase ' . $home_root . "\n";
	$rules .= 'RewriteCond %{REQUEST_METHOD} GET' . "\n";
	$rules .= 'RewriteCond %{QUERY_STRING} !.*=.*' . "\n";
	$rules .= 'RewriteCond %{HTTP:Cookie} !^.*(' . get_rocket_cookies_not_cached() . ').*$' . "\n";
	$rules .= 'RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).* [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).* [NC]' . "\n";
	$rules .= 'RewriteCond %{HTTPS} off' . "\n";
	$rules .= 'RewriteCond %{DOCUMENT_ROOT}/'. $cache_root .'%{HTTP_HOST}%{REQUEST_URI}index.html -f' . "\n";
	$rules .= 'RewriteRule ^(.*) /' . $cache_root . '%{HTTP_HOST}%{REQUEST_URI}index.html [L]' . "\n";
	$rules .= '</IfModule>' . "\n\n";

	return $rules;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function get_rocket_htaccess_mod_deflate()
{

	$rules = '# Gzip compression' . "\n";
	$rules .= '<IfModule mod_deflate.c>' . "\n";
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
	$rules .= '</IfModule>' . "\n\n";

	return $rules;

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
 
function get_rocket_htaccess_expires() {
	
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
	  $rules .= 'ExpiresByType application/javascript    "access plus 1 year"' . "\n";
	$rules .= '</IfModule>' . "\n\n";
	
	return $rules;
	
}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function get_rocket_htaccess_charset()
{

	$rules = "# Use UTF-8 encoding for anything served text/plain or text/html\n";
	$rules .= "AddDefaultCharset UTF-8\n";
	$rules .= "# Force UTF-8 for a number of file formats\n";
	$rules .= "AddCharset utf-8 .atom .css .js .json .rss .vtt .xml\n\n";

	return $rules;
}



/**
 * TO DO - Description
 *
 * since 1.0
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

	return $rules;
}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_override_mod_rewrite_rules( $rules )
{

	$rules = explode( "\n", $rules );

	$i=0;
	foreach( $rules as $rule )
	{

		if( strstr( $rule, '^index\.php$ -' ) != false )
			$rules[$i] = 'RewriteRule ^index\.php$ ' . ltrim( str_replace( get_option( 'siteurl' ), '', WP_PLUGIN_URL ), '/' )  . '/wp-rocket/bootstrap.php [L]' . "\n";


		if( strstr( $rule, '/index.php' ) != false )
			$rules[$i] = 'RewriteRule . ' . str_replace( get_option( 'siteurl' ), '', WP_PLUGIN_URL )  . '/wp-rocket/bootstrap.php [L]' . "\n";

		$i++;

	}

	return implode( "\n" , $rules );

}