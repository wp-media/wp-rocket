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
	$cache_root = trim( str_replace( site_url( '/' ), '', WP_ROCKET_CACHE_URL ), '/' );

	$rules  = '<IfModule mod_rewrite.c>' . "\n";
	$rules .= 'RewriteEngine On' . "\n";
	$rules .= 'RewriteBase ' . $home_root . "\n";
	$rules .= 'RewriteCond %{REQUEST_METHOD} GET' . "\n";
	$rules .= 'RewriteCond %{QUERY_STRING} !.*=.*' . "\n";
	$rules .= 'RewriteCond %{HTTP:Cookie} !^.*(' . get_rocket_cookies_not_cached() . ').*$' . "\n";
	$rules .= 'RewriteCond %{HTTPS} off' . "\n";
	$rules .= 'RewriteCond %{DOCUMENT_ROOT}/'. $cache_root .'/%{HTTP_HOST}%{REQUEST_URI}index.html -f' . "\n";
	$rules .= 'RewriteRule ^(.*) /' . $cache_root . '/%{HTTP_HOST}%{REQUEST_URI}index.html [L]' . "\n";
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