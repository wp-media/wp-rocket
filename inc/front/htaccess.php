<?php


/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function flush_rocket_htaccess()
{

	$htaccess_file = ABSPATH . '.htaccess';

	if( file_exists( $htaccess_file ) && is_writeable( $htaccess_file ) )
	{
		
		// Get content of .htaccess file
		$ftmp = file_get_contents( $htaccess_file );
		
		// Delete the WP Rocket marker
		$ftmp = preg_replace( '/# BEGIN WP Rocket(.*)# END WP Rocket/isUe', '', $ftmp );
		
		// Recreate WP Rocket marker
		$file  = '# BEGIN WP Rocket' . "\n";
		$file .= get_rocket_htaccess_charset();
		$file .= get_rocket_mod_headers();
		$file .= get_rocket_htaccess_gzip_encoding();
		$file .= get_rocket_mod_rewrite();
		$file .= '# END WP Rocket'. "\n\n";

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
function get_rocket_mod_rewrite()
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
	$rules .= 'RewriteCond %{HTTP:Accept-Encoding} gzip' . "\n";
	$rules .= 'RewriteCond %{HTTPS} off' . "\n";
	$rules .= 'RewriteCond %{DOCUMENT_ROOT}/'. $cache_root .'%{REQUEST_URI}index.html.gz -f' . "\n";
	$rules .= 'RewriteRule ^(.*) /' . $cache_root . '%{REQUEST_URI}index.html.gz [L]' . "\n";
	$rules .= '</IfModule>' . "\n";

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
function get_rocket_mod_headers()
{

	$rules = "# FileETag None is not enough for every server.\n";
	$rules .= "<IfModule mod_headers.c>\n";
	$rules .= "Header unset ETag\n";
	$rules .= "</IfModule>\n\n";

	return $rules;
}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function get_rocket_htaccess_gzip_encoding()
{

	$rules = "<FilesMatch '\.html.gz$'>\n";
	$rules .= "AddEncoding x-gzip .gz\n";
	$rules .= "AddType text/html .gz\n";
	$rules .= "</FilesMatch>\n\n";

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