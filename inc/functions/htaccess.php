<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Used to flush the .htaccess file
 *
 * @since 1.1.0 Remove empty spacings when .htaccess is generated
 * @since 1.0
 *
 * @param bool $force (default: false).
 * @return void
 */
function flush_rocket_htaccess( $force = false ) {
	global $is_apache;

	if ( ! $is_apache ) {
		return;
	}

	$rules = '';
	$htaccess_file = get_home_path() . '.htaccess';

	if ( rocket_direct_filesystem()->is_writable( $htaccess_file ) ) {
		// Get content of .htaccess file.
		$ftmp = rocket_direct_filesystem()->get_contents( $htaccess_file );

		// Remove the WP Rocket marker.
		$ftmp = preg_replace( '/# BEGIN WP Rocket(.*)# END WP Rocket/isU', '', $ftmp );

		/**
		 * Determine if empty lines should be removed in the .htaccess file
		 *
		 * @since 2.10.7
		 * @author Remy Perona
		 * @param boolean $remove_empty_lines True to remove, false otherwise.
		 */
		if ( apply_filters( 'rocket_remove_empty_lines', true ) ) {
			$ftmp = str_replace( "\n\n" , "\n" , $ftmp );
		}

		if ( false === $force ) {
			$rules = get_rocket_htaccess_marker();
		}

		// Update the .htacces file.
		rocket_put_content( $htaccess_file, $rules . $ftmp );
	}
}

/**
 * Test if a server error is triggered by our rules
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @param (string) $rules_name The rules block to test.
 *
 * @return (object|bool) Return true if the server does not trigger an error 500, false otherwise.
 *                       Return a WP_Error object if the sandbox creation fails or if the HTTP request fails.
 */
function rocket_htaccess_rules_test( $rules_name ) {
	/**
	 * Filters the request arguments
	 *
	 * @author Remy Perona
	 * @since 2.10
	 *
	 * @param array $args Array of argument for the request.
	 */
	$request_args = apply_filters(
		'rocket_htaccess_rules_test_args', array(
			'redirection' => 0,
			'timeout'     => 5,
			'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
			'user-agent'  => 'wprocketbot',
			'cookies'     => $_COOKIE,
		)
	);

	$response = wp_remote_get( site_url( WP_ROCKET_URL . 'tests/' . $rules_name . '/index.html' ), $request_args );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	return 500 !== wp_remote_retrieve_response_code( $response );
}

/**
 * Return the markers for htacces rules
 *
 * @since 1.0
 *
 * @return string $marker Rules that will be printed
 */
function get_rocket_htaccess_marker() {
	// Recreate WP Rocket marker.
	$marker  = '# BEGIN WP Rocket v' . WP_ROCKET_VERSION . PHP_EOL;

	/**
	 * Add custom rules before rules added by WP Rocket
	 *
	 * @since 2.6
	 *
	 * @param string $before_marker The content of all rules.
	*/
	$marker .= apply_filters( 'before_rocket_htaccess_rules', '' );

	$marker .= get_rocket_htaccess_charset();
	$marker .= get_rocket_htaccess_etag();
	$marker .= get_rocket_htaccess_web_fonts_access();
	$marker .= get_rocket_htaccess_files_match();
	$marker .= get_rocket_htaccess_mod_expires();
	$marker .= get_rocket_htaccess_mod_deflate();

	/** This filter is documented in inc/front/process.php */
	if ( apply_filters( 'do_rocket_generate_caching_files', true ) && ! is_rocket_generate_caching_mobile_files() ) {
		$marker .= get_rocket_htaccess_mod_rewrite();
	}

	/**
	 * Add custom rules after rules added by WP Rocket
	 *
	 * @since 2.6
	 *
	 * @param string $after_marker The content of all rules.
	*/
	$marker .= apply_filters( 'after_rocket_htaccess_rules', '' );

	$marker .= '# END WP Rocket' . PHP_EOL;

	/**
	 * Filter rules added by WP Rocket in .htaccess
	 *
	 * @since 2.1
	 *
	 * @param string $marker The content of all rules.
	*/
	$marker = apply_filters( 'rocket_htaccess_marker', $marker );

	return $marker;
}

/**
 * Rewrite rules to serve the cache file
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_mod_rewrite() {
	// No rewrite rules for multisite.
	if ( is_multisite() ) {
		return;
	}

	// No rewrite rules for Korean.
	if ( defined( 'WPLANG' ) && 'ko_KR' === WPLANG || 'ko_KR' === get_locale() ) {
		return;
	}

	// Get root base.
	$home_root = rocket_extract_url_component( home_url(), PHP_URL_PATH );
	$home_root = isset( $home_root ) ? trailingslashit( $home_root ) : '/';

	$site_root = rocket_extract_url_component( site_url(), PHP_URL_PATH );
	$site_root = isset( $site_root ) ? trailingslashit( $site_root ) : '';

	// Get cache root.
	if ( strpos( ABSPATH, WP_ROCKET_CACHE_PATH ) === false ) {
		$cache_root = str_replace( $_SERVER['DOCUMENT_ROOT'] , '', WP_ROCKET_CACHE_PATH );
	} else {
		$cache_root = $site_root . str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH );
	}

	/**
	  * Replace the dots by underscores to avoid some bugs on some shared hosting services on filenames (not multisite compatible!)
	  *
	  * @since 1.3.0
	  *
	  * @param bool true will replace the . by _.
	 */
	$http_host = apply_filters( 'rocket_url_no_dots', false ) ? rocket_remove_url_protocol( home_url() ) : '%{HTTP_HOST}';

	/**
	  * Allow the path to be fully printed or dependant od %DOCUMENT_ROOT (forced for 1&1 by default)
	  *
	  * @since 1.3.0
	  *
	  * @param bool true will force the path to be full.
	 */
	$is_1and1_or_force = apply_filters( 'rocket_force_full_path', strpos( $_SERVER['DOCUMENT_ROOT'], '/kunden/' ) === 0 );

	$rules = '';
	$gzip_rules = '';
	$enc = '';

	/**
	  * Allow to serve gzip cache file
	  *
	  * @since 2.4
	  *
	  * @param bool true will force to serve gzip cache file.
	 */
	if ( function_exists( 'gzencode' ) && apply_filters( 'rocket_force_gzip_htaccess_rules', true ) ) {
		$rules = '<IfModule mod_mime.c>' . PHP_EOL;
			$rules .= 'AddType text/html .html_gzip' . PHP_EOL;
			$rules .= 'AddEncoding gzip .html_gzip' . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL;
		$rules .= '<IfModule mod_setenvif.c>' . PHP_EOL;
			$rules .= 'SetEnvIfNoCase Request_URI \.html_gzip$ no-gzip' . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;

		$gzip_rules .= 'RewriteCond %{HTTP:Accept-Encoding} gzip' . PHP_EOL;
		$gzip_rules .= 'RewriteRule .* - [E=WPR_ENC:_gzip]' . PHP_EOL;

		$enc = '%{ENV:WPR_ENC}';
	}

	$rules .= '<IfModule mod_rewrite.c>' . PHP_EOL;
	$rules .= 'RewriteEngine On' . PHP_EOL;
	$rules .= 'RewriteBase ' . $home_root . PHP_EOL;
	$rules .= get_rocket_htaccess_ssl_rewritecond();
	$rules .= $gzip_rules;
	$rules .= 'RewriteCond %{REQUEST_METHOD} GET' . PHP_EOL;
	$rules .= 'RewriteCond %{QUERY_STRING} =""' . PHP_EOL;

	$cookies = get_rocket_cache_reject_cookies();
	if ( $cookies ) {
		$rules .= 'RewriteCond %{HTTP:Cookie} !(' . $cookies . ') [NC]' . PHP_EOL;
	}

	$uri = get_rocket_cache_reject_uri();
	if ( $uri ) {
		$rules .= 'RewriteCond %{REQUEST_URI} !^(' . $uri . ')$ [NC]' . PHP_EOL;
	}

	$rules .= ! is_rocket_cache_mobile() ? get_rocket_htaccess_mobile_rewritecond() : '';

	$ua = get_rocket_cache_reject_ua();
	if ( $ua ) {
		$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^(' . $ua . ').* [NC]' . PHP_EOL;
	}

	if ( $is_1and1_or_force ) {
		$rules .= 'RewriteCond "' . str_replace( '/kunden/', '/', WP_ROCKET_CACHE_PATH ) . $http_host . '%{REQUEST_URI}/index%{ENV:WPR_SSL}.html' . $enc . '" -f' . PHP_EOL;
	} else {
		$rules .= 'RewriteCond "%{DOCUMENT_ROOT}/' . ltrim( $cache_root, '/' ) . $http_host . '%{REQUEST_URI}/index%{ENV:WPR_SSL}.html' . $enc . '" -f' . PHP_EOL;
	}

	$rules .= 'RewriteRule .* "' . $cache_root . $http_host . '%{REQUEST_URI}/index%{ENV:WPR_SSL}.html' . $enc . '" [L]' . PHP_EOL;
	$rules .= '</IfModule>' . PHP_EOL;

	/**
	 * Filter rewrite rules to serve the cache file
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_mod_rewrite', $rules );

	return $rules;
}

/**
 * Rules for detect mobile version
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_mobile_rewritecond() {
	// No rewrite rules for multisite.
	if ( is_multisite() ) {
		return;
	}

	$rules = 'RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]' . PHP_EOL;
	$rules .= 'RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]' . PHP_EOL;
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).* [NC]' . PHP_EOL;
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).* [NC]' . PHP_EOL;

	/**
	 * Filter rules for detect mobile version
	 *
	 * @since 2.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_mobile_rewritecond', $rules );

	return $rules;
}

/**
 * Rules for SSL requests
 *
 * @since 2.7 Added rewrite condition for `%{HTTP:X-Forwarded-Proto}`.
 * @since 2.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_ssl_rewritecond() {
	$rules  = 'RewriteCond %{HTTPS} on [OR]' . PHP_EOL;
	$rules .= 'RewriteCond %{SERVER_PORT} ^443$ [OR]' . PHP_EOL;
	$rules .= 'RewriteCond %{HTTP:X-Forwarded-Proto} https' . PHP_EOL;
	$rules .= 'RewriteRule .* - [E=WPR_SSL:-https]' . PHP_EOL;

	/**
	 * Filter rules for SSL requests
	 *
	 * @since 2.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_ssl_rewritecond', $rules );

	return $rules;
}

/**
 * Rules to improve performances with GZIP Compression
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_mod_deflate() {
	$rules = '# Gzip compression' . PHP_EOL;
	$rules .= '<IfModule mod_deflate.c>' . PHP_EOL;
		$rules .= '# Active compression' . PHP_EOL;
		$rules .= 'SetOutputFilter DEFLATE' . PHP_EOL;
		$rules .= '# Force deflate for mangled headers' . PHP_EOL;
		$rules .= '<IfModule mod_setenvif.c>' . PHP_EOL;
			$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
			$rules .= 'SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding' . PHP_EOL;
			$rules .= 'RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding' . PHP_EOL;
			$rules .= '# Don’t compress images and other uncompressible content' . PHP_EOL;
			$rules .= 'SetEnvIfNoCase Request_URI \\' . PHP_EOL;
			$rules .= '\\.(?:gif|jpe?g|png|rar|zip|exe|flv|mov|wma|mp3|avi|swf|mp?g|mp4|webm|webp|pdf)$ no-gzip dont-vary' . PHP_EOL;
			$rules .= '</IfModule>' . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;
		$rules .= '# Compress all output labeled with one of the following MIME-types' . PHP_EOL;
		$rules .= '<IfModule mod_filter.c>' . PHP_EOL;
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
		                          text/xml' . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL;
		$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
			 $rules .= 'Header append Vary: Accept-Encoding' . PHP_EOL;
	   $rules .= '</IfModule>' . PHP_EOL;
	$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;

	/**
	 * Filter rules to improve performances with GZIP Compression
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_mod_deflate', $rules );

	return $rules;
}

/**
 * Rules to improve performances with Expires Headers
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_mod_expires() {
	$rules = <<<HTACCESS
# Expires headers (for better cache control)
<IfModule mod_expires.c>
	ExpiresActive on
	# Perhaps better to whitelist expires rules? Perhaps.
	ExpiresDefault                              "access plus 1 month"
	# cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)
	ExpiresByType text/cache-manifest           "access plus 0 seconds"
	# Your document html
	ExpiresByType text/html                     "access plus 0 seconds"
	# Data
	ExpiresByType text/xml                      "access plus 0 seconds"
	ExpiresByType application/xml               "access plus 0 seconds"
	ExpiresByType application/json              "access plus 0 seconds"
	# Feed
	ExpiresByType application/rss+xml           "access plus 1 hour"
	ExpiresByType application/atom+xml          "access plus 1 hour"
	# Favicon (cannot be renamed)
	ExpiresByType image/x-icon                  "access plus 1 week"
	# Media: images, video, audio
	ExpiresByType image/gif                     "access plus 1 month"
	ExpiresByType image/png                     "access plus 1 month"
	ExpiresByType image/jpeg                    "access plus 1 month"
	ExpiresByType image/webp                    "access plus 1 month"
	ExpiresByType video/ogg                     "access plus 1 month"
	ExpiresByType audio/ogg                     "access plus 1 month"
	ExpiresByType video/mp4                     "access plus 1 month"
	ExpiresByType video/webm                    "access plus 1 month"
	# HTC files  (css3pie)
	ExpiresByType text/x-component              "access plus 1 month"
	# Webfonts
	ExpiresByType application/x-font-ttf        "access plus 1 month"
	ExpiresByType font/opentype                 "access plus 1 month"
	ExpiresByType application/x-font-woff       "access plus 1 month"
	ExpiresByType application/x-font-woff2      "access plus 1 month"
	ExpiresByType image/svg+xml                 "access plus 1 month"
	ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
	# CSS and JavaScript
	ExpiresByType text/css                      "access plus 1 year"
	ExpiresByType application/javascript        "access plus 1 year"
</IfModule>

HTACCESS;

	/**
	 * Filter rules to improve performances with Expires Headers
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	 */
	$rules = apply_filters( 'rocket_htaccess_mod_expires', $rules );

	return $rules;
}

/**
 * Rules for default charset on static files
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_charset() {
	// Get charset of the blog.
	$charset = preg_replace( '/[^a-zA-Z0-9_\-\.:]+/', '', get_bloginfo( 'charset', 'display' ) );

	$rules = "# Use $charset encoding for anything served text/plain or text/html" . PHP_EOL;
	$rules .= "AddDefaultCharset $charset" . PHP_EOL;
	$rules .= "# Force $charset for a number of file formats" . PHP_EOL;
	$rules .= '<IfModule mod_mime.c>' . PHP_EOL;
		$rules .= "AddCharset $charset .atom .css .js .json .rss .vtt .xml" . PHP_EOL;
	$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;

	/**
	 * Filter rules for default charset on static files
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_charset', $rules );

	return $rules;
}

/**
 * Rules for cache control
 *
 * @since 1.1.6
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_files_match() {
	$rules = '<IfModule mod_alias.c>' . PHP_EOL;
		$rules .= '<FilesMatch "\.(html|htm|rtf|rtx|txt|xsd|xsl|xml)$">' . PHP_EOL;
			$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
				 $rules .= 'Header set X-Powered-By "WP Rocket/' . WP_ROCKET_VERSION . '"' . PHP_EOL;
				 $rules .= 'Header unset Pragma' . PHP_EOL;
				 $rules .= 'Header append Cache-Control "public"' . PHP_EOL;
				 $rules .= 'Header unset Last-Modified' . PHP_EOL;
			$rules .= '</IfModule>' . PHP_EOL;
		$rules .= '</FilesMatch>' . PHP_EOL . PHP_EOL;
		$rules .= '<FilesMatch "\.(css|htc|js|asf|asx|wax|wmv|wmx|avi|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|mpp|otf|odb|odc|odf|odg|odp|ods|odt|ogg|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|wav|wma|wri|xla|xls|xlsx|xlt|xlw|zip)$">' . PHP_EOL;
			$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
				$rules .= 'Header unset Pragma' . PHP_EOL;
				$rules .= 'Header append Cache-Control "public"' . PHP_EOL;
			$rules .= '</IfModule>' . PHP_EOL;
		$rules .= '</FilesMatch>' . PHP_EOL;
	$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;

	/**
	 * Filter rules for cache control
	 *
	 * @since 1.1.6
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_files_match', $rules );

	return $rules;
}

/**
 * Rules to remove the etag
 *
 * @since 1.0
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_etag() {
	$rules  = '# FileETag None is not enough for every server.' . PHP_EOL;
	$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
	$rules .= 'Header unset ETag' . PHP_EOL;
	$rules .= '</IfModule>' . PHP_EOL . PHP_EOL;
	$rules .= '# Since we’re sending far-future expires, we don’t need ETags for static content.' . PHP_EOL;
	$rules .= '# developer.yahoo.com/performance/rules.html#etags' . PHP_EOL;
	$rules .= 'FileETag None' . PHP_EOL . PHP_EOL;

	/**
	 * Filter rules to remove the etag
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_etag', $rules );

	return $rules;
}

/**
 * Rules to Cross-origin fonts sharing when CDN is used
 *
 * @since 2.4
 *
 * @return string $rules Rules that will be printed
 */
function get_rocket_htaccess_web_fonts_access() {
	if ( ! get_rocket_option( 'cdn', false ) ) {
		return;
	}

	$rules  = '# Send CORS headers if browsers request them; enabled by default for images.' . PHP_EOL;
	$rules  .= '<IfModule mod_setenvif.c>' . PHP_EOL;
	  $rules  .= '<IfModule mod_headers.c>' . PHP_EOL;
		$rules  .= '# mod_headers, y u no match by Content-Type?!' . PHP_EOL;
		$rules  .= '<FilesMatch "\.(cur|gif|png|jpe?g|svgz?|ico|webp)$">' . PHP_EOL;
		  $rules  .= 'SetEnvIf Origin ":" IS_CORS' . PHP_EOL;
		  $rules  .= 'Header set Access-Control-Allow-Origin "*" env=IS_CORS' . PHP_EOL;
		$rules  .= '</FilesMatch>' . PHP_EOL;
	  $rules  .= '</IfModule>' . PHP_EOL;
	$rules  .= '</IfModule>' . PHP_EOL . PHP_EOL;

	$rules  .= '# Allow access to web fonts from all domains.' . PHP_EOL;
	$rules  .= '<FilesMatch "\.(eot|otf|tt[cf]|woff2?)$">' . PHP_EOL;
		$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
			$rules .= 'Header set Access-Control-Allow-Origin "*"' . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL;
	$rules .= '</FilesMatch>' . PHP_EOL . PHP_EOL;

	/**
	 * Filter rules to Cross-origin fonts sharing
	 *
	 * @since 1.0
	 *
	 * @param string $rules Rules that will be printed.
	*/
	$rules = apply_filters( 'rocket_htaccess_web_fonts_access', $rules );

	return $rules;
}
