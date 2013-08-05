<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Used to flush the .htaccess file
 *
 * since 1.1.0 Remove empty spacings then .htaccess is generated
 * since 1.0
 *
 */

function flush_rocket_htaccess( $force = false )
{

	$rules = '';
	$htaccess_file = get_real_file_to_edit( '.htaccess' );

	if( file_exists( $htaccess_file ) && is_writeable( $htaccess_file ) )
	{

		// Get content of .htaccess file
		$ftmp = file_get_contents( $htaccess_file );

		// Remove the WP Rocket marker
		$ftmp = preg_replace( '/# BEGIN WP Rocket(.*)# END WP Rocket/isUe', '', $ftmp );

		// Remove empty spacings
		$ftmp = str_replace( "\n\n" , "\n" , $ftmp );

		if( $force === false  )
			$rules = get_rocket_htaccess_marker();


		// Update the .htacces file
		file_put_contents( $htaccess_file , $rules . $ftmp );

	}

}



/**
 * Return the markers for htacces rules
 *
 * since 1.0
 *
 */
function get_rocket_htaccess_marker()
{

	// Recreate WP Rocket marker
	$marker  = '# BEGIN WP Rocket' . "\n";
	$marker .= get_rocket_htaccess_charset();
	$marker .= get_rocket_htaccess_etag();
	$marker .= get_rocket_htaccess_files_match();
	$marker .= get_rocket_htaccess_mod_expires();
	$marker .= get_rocket_htaccess_mod_deflate();
	$marker .= get_rocket_htaccess_mod_rewrite();
	$marker .= '# END WP Rocket' . "\n";

	return $marker;
}


/**
 * Add somes rules need by the plugin
 *
 * since 1.0
 *
 */

function get_rocket_htaccess_mod_rewrite()
{

	// Get root base
	$home_root = parse_url(home_url());
	$home_root = isset( $home_root['path'] ) ? trailingslashit($home_root['path']) : '/';

	$site_root = parse_url( site_url() );
	$site_root = isset( $site_root['path'] ) ? trailingslashit($site_root['path']) : '';

	// Get cache root
	$cache_root = $site_root . str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH );

	// Set correct HOST dependong on hook (not multisite compatible!)
	$HTTP_HOST = apply_filters( 'rocket_url_no_dots', false ) ? rocket_remove_url_protocol( home_url() ) : '%{HTTP_HOST}';

	$is_1and1_or_force = apply_filters( 'rocket_force_full_path', strpos( $_SERVER['DOCUMENT_ROOT'], '/kunden/' ) === 0 );

	$rules  = '<IfModule mod_rewrite.c>' . "\n";
	$rules .= 'RewriteEngine On' . "\n";
	$rules .= 'RewriteBase ' . $home_root . "\n";
	$rules .= 'RewriteCond %{REQUEST_METHOD} GET' . "\n";
	$rules .= 'RewriteCond %{QUERY_STRING} !.*=.*' . "\n";
	$rules .= 'RewriteCond %{HTTP:Cookie} !(' . get_rocket_cookies_not_cached() . ') [NC]' . "\n";
	$rules .= 'RewriteCond %{REQUEST_URI} !^(' . get_rocket_pages_not_cached() . ')$ [NC]' . "\n";
	$rules .= !is_rocket_cache_mobile() ? get_rocket_htaccess_mobile_rewritecond() : '';
	$rules .= 'RewriteCond %{HTTPS} off' . "\n";
	if( $is_1and1_or_force )
		$rules .= 'RewriteCond "' . str_replace( '/kunden/', '/', WP_ROCKET_CACHE_PATH ) . $HTTP_HOST . '%{REQUEST_URI}/index.html" -f' . "\n";
	else
		$rules .= 'RewriteCond "%{DOCUMENT_ROOT}/'. basename( dirname( WP_ROCKET_CACHE_PATH ) ) . '/' . basename( WP_ROCKET_CACHE_PATH ) .'/'.$HTTP_HOST.'%{REQUEST_URI}/index.html" -f' . "\n";
	$rules .= 'RewriteRule ^(.*) ' . $cache_root . $HTTP_HOST . '%{REQUEST_URI}/index.html [L]' . "\n";
	$rules .= '</IfModule>' . "\n";
	$rules = apply_filters( 'rocket_htaccess_mod_rewrite', $rules );

	return $rules;

}



/**
 * Other rules for mobile version
 *
 * since 1.0
 *
 */

function get_rocket_htaccess_mobile_rewritecond()
{

	$rules = 'RewriteCond %{HTTP_USER_AGENT} !(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge\ |maemo|midp|mmp|netfront|opera\ m(ob|in)i|palm(\ os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows\ (ce|phone)|xda|xiino [NC,OR]' . "\n";
	$rules .= 'RewriteCond %{HTTP_USER_AGENT} !^(1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a\ wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r\ |s\ )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1\ u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp(\ i|ip)|hs\-c|ht(c(\-|\ |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac(\ |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt(\ |\/)|klon|kpt\ |kwc\-|kyo(c|k)|le(no|xi)|lg(\ g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-|\ |o|v)|zz)|mt(50|p1|v\ )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v\ )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-|\ )|webc|whit|wi(g\ |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-) [NC]' . "\n";

	return $rules;
}



/**
 * Other rules again to improve performances
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
		$rules .= '<IfModule mod_headers.c>' . "\n";
             $rules .= 'Header append Vary User-Agent env=!dont-vary' . "\n";
             $rules .= 'Header append Vary Accept-Encoding' . "\n";
       $rules .= '</IfModule>' . "\n";
	$rules .= '</IfModule>' . "\n\n";
	$rules = apply_filters( 'rocket_htaccess_mod_deflate', $rules );

	return $rules;

}



/**
 * Other rules to improve performances again
 *
 * since 1.0
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
 * since 1.0
 *
 */
function get_rocket_htaccess_charset()
{

	$rules = "# Use UTF-8 encoding for anything served text/plain or text/html\n";
	$rules .= "AddDefaultCharset UTF-8\n";
	$rules .= "# Force UTF-8 for a number of file formats\n";
	$rules .= "AddCharset utf-8 .atom .css .js .json .rss .vtt .xml\n\n";
	$rules = apply_filters( 'rocket_htaccess_charset', $rules );

	return $rules;
}



/**
 * Add some files match rules
 *
 * since 1.1.6
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
    $rules = apply_filters( 'rocket_htaccess_etag', $rules );

	return $rules;
}