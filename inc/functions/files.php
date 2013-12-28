<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Generate the content of advanced-cache.php file
 *
 * @since 2.0.3
 *
 */

function get_rocket_advanced_cache_file()
{

	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";

	// Get cache path
	$buffer .= '$rocket_cache_path = \'' . WP_ROCKET_CACHE_PATH . '\'' . ";\n";

	// Get config path
	$buffer .= '$rocket_config_path = \'' . WP_ROCKET_CONFIG_PATH . '\'' . ";\n";

	// Include the process file in buffer
	$buffer .= 'include( \''. WP_ROCKET_FRONT_PATH . 'process.php' . '\' );';

	return $buffer;

}



/**
 * Create advanced-cache.php file
 *
 * @since 2.0
 *
 */


function rocket_generate_advanced_cache_file()
{
	
	$buffer  = get_rocket_advanced_cache_file();
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', $buffer );

}




/**
 * Generates the configuration file for the current domain based on the values ​​of options
 *
 * @since 2.0
 *
 */

function get_rocket_config_file()
{

	$options = get_option( WP_ROCKET_SLUG );

	if( !$options )
		return;

	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";
	$buffer .= '$rocket_cookie_hash = \'' . COOKIEHASH . '\'' . ";\n";

	foreach( $options as $option => $value )
	{

		if( $option == 'cache_ssl' || $option == 'cache_mobile' || $option == 'secret_cache_key' )
			$buffer .= '$rocket_' . $option . ' = \'' . $value . '\';' . "\n";

		if( $option == 'cache_reject_uri' )
			$buffer .= '$rocket_' . $option . ' = \'' . get_rocket_cache_reject_uri() . '\';' . "\n";

		if( $option == 'cache_reject_cookies' )
		{
			$cookies = get_rocket_cache_reject_cookies();
			$cookies = get_rocket_option( 'cache_logged_user' ) ? trim( str_replace( 'wordpress_logged_in_', '', $cookies ), '|' ) : $cookies;
			$buffer .= '$rocket_' . $option . ' = \'' . $cookies . '\';' . "\n";
		}
	}
	
	if( apply_filters( 'rocket_url_no_dots', false ) ) 
	{
		$buffer .= '$rocket_url_no_dots = \'1\';';
	}
	
	$url = parse_url( rtrim( home_url(), '/' ) );

	if( !isset( $url['path'] ) )
	{
		$config_file_path = WP_ROCKET_CONFIG_PATH . $url['host'] . '.php';
	}
	else
	{

		$home_url_path       = explode( '/', trim( $url['path'], '/' ) );
		$home_url_start_path = reset( ( $home_url_path ) );
		$home_url_end_path   = end  ( ( $home_url_path ) );

		$config_dir_path     = WP_ROCKET_CONFIG_PATH . $url['host'];

		if( $home_url_start_path != $home_url_end_path )
			$config_dir_path = $config_dir_path . '/' . trim( str_replace( $home_url_end_path , '', $url['path'] ), '/' );

		if( !is_dir( $config_dir_path ) )
			rocket_mkdir_p( $config_dir_path );

		$config_file_name = $home_url_end_path . '.php';
		$config_file_path = $config_dir_path . '/' . $config_file_name;
	}


	return array( $config_file_path, $buffer );

}



/**
 * Create the current config domain file
 * For example, if home_url() return example.com, the config domain file will be in /config/example.com
 *
 * @since 2.0
 *
 */

function rocket_generate_config_file()
{
	
	list( $config_file_path, $buffer ) = get_rocket_config_file();
	rocket_put_content( $config_file_path , $buffer );

}



/**
 * Added or set the value of the WP_CACHE constant
 *
 * @since 2.0
 *
 */

function set_rocket_wp_cache_define( $enable = true )
{

	// If WP_CACHE is already define, return to get a coffee
	if( $enable && defined( 'WP_CACHE' ) && WP_CACHE  )
		return;

	// Get content of the config file
	$config_file = @file_get_contents( get_home_path() . 'wp-config.php' );

    if ( !$config_file )
        return;

	// Get the value of WP_CACHE constant
	$enable = $enable ? 'true' : 'false';

	// Get the content of the WP_CACHE constant added by WP Rocket
	$define = "/** Enable Cache */\r\n" . "define('WP_CACHE', $enable); // Added by WP Rocket\r\n";

	$config_file = preg_replace( "~\\/\\*\\* Enable Cache \\*\\*?\\/.*?\\/\\/ Added by WP Rocket(\r\n)*~s", '', $config_file );
    $config_file = preg_replace( "~(\\/\\/\\s*)?define\\s*\\(\\s*['\"]?WP_CACHE['\"]?\\s*,.*?\\)\\s*;+\\r?\\n?~is", '', $config_file );
	$config_file = preg_replace( '~<\?(php)?~', "\\0\r\n" . $define, $config_file );

	rocket_put_content( ABSPATH . 'wp-config.php', $config_file );

}



/**
 * Delete one or several cache files
 *
 * @since 2.0 Delete cache files for all users
 * @since 1.0
 *
 */

function rocket_clean_files( $urls )
{
	if( is_string( $urls ) )
		$urls = (array)$urls;

	$urls = apply_filters( 'rocket_clean_files', $urls );

    foreach( array_filter($urls) as $url )
    {

		do_action( 'before_rocket_clean_file', $url );

		if( $dirs = glob( WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( $url ) ) )
		{
			foreach( $dirs as $dir )
				rocket_rrmdir( $dir );
		}

		do_action( 'after_rocket_clean_file', $url );

	}
}



/**
 * Remove the home cache file and pagination
 *
 * @since 2.0 Delete cache files for all users
 * @since 1.0
 *
 */

function rocket_clean_home()
{
	$root = WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( home_url() );

	do_action( 'before_rocket_clean_home', $root );

	// Delete homepage
	if( $files = glob( $root . '*/index.html' ) )
	{
		foreach( $files as $file )
			@unlink( $file );
	}

	// Delete homepage pagination
	if( $dirs = glob( $root . '*/' . $GLOBALS['wp_rewrite']->pagination_base ) )
	{
		foreach( $dirs as $dir )
			rocket_rrmdir( $dir );
	}

    do_action( 'after_rocket_clean_home', $root );
}



/**
 * Remove all cache files of the domain
 *
 * @since 2.0 Delete domain cache files for all users
 * @since 1.0
 *
 */

function rocket_clean_domain()
{
	$domain = WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( home_url() );

	do_action( 'before_rocket_clean_domain', $domain );

	// Delete cache domain files
	if( $dirs = glob( $domain . '*' ) )
	{
		foreach( $dirs as $dir )
			rocket_rrmdir( $dir );
	}

    do_action( 'after_rocket_clean_domain', $domain );
}



/**
 * Remove only cache files of selected lang
 *
 * @since 2.0
 *
 */

function rocket_clean_domain_for_selected_lang( $lang )
{

	do_action( 'before_purge_cache_for_selected_lang' , $lang );

	list( $host, $path ) = get_rocket_parse_url_for_lang( $lang );
	if( $dirs = glob( WP_ROCKET_CACHE_PATH . $host . '*/' . $path ) )
	{
		foreach( $dirs as $dir )
			rocket_rrmdir( $dir, get_rocket_langs_to_preserve( $lang ) );
	}

	do_action( 'after_purge_cache_for_selected_lang' , $lang );
}



/**
 * Remove cache files of all langs
 *
 * @since 2.0
 *
 */

function rocket_clean_domain_for_all_langs()
{

	$langs = get_rocket_all_active_langs();

	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{
		global $sitepress;
		$langs = array_keys( $langs );
	}

	do_action( 'before_rocket_clean_domain_for_all_langs' , $langs );

	// Remove all cache langs
	foreach ( $langs as $lang )
	{
		list( $host ) = get_rocket_parse_url_for_lang( $lang );
		if( $dirs = glob( WP_ROCKET_CACHE_PATH . $host . '*' ) )
		{
			foreach( $dirs as $dir )
				rocket_rrmdir( $dir );
		}
	}

	do_action( 'after_rocket_clean_domain_for_all_langs' , $langs );
}



/**
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 */

function rocket_rrmdir( $dir, $dirs_to_preserve = array() )
{

	$dir = rtrim( $dir, '/' );

	do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve );

	if( !is_dir( $dir ) )
	{
		@unlink( $dir );
		return;
	};

    if( $dirs = glob( $dir . '/*' ) )
    {

		$keys = array();
		foreach( $dirs_to_preserve as $dir_to_preserve )
		{
			$matches = preg_grep( "#^$dir_to_preserve$#" , $dirs );
			$keys[] = reset( $matches );
		}

		$dirs = array_diff( $dirs, array_filter( $keys ) );
		foreach( $dirs as $dir )
		{
			if( is_dir( $dir ) )
				rocket_rrmdir( $dir, $dirs_to_preserve );
			else
				@unlink( $dir );
		}
	}

	@rmdir($dir);

	do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve );
}



/**
 * Directory creation based on WordPress Filesystem
 *
 * @since 1.3.4
 *
 */

function rocket_mkdir( $dir )
{

	global $wp_filesystem;
    if( !$wp_filesystem )
    {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		$wp_filesystem = new WP_Filesystem_Direct( new StdClass() );
	}
	return $wp_filesystem->mkdir( $dir, CHMOD_WP_ROCKET_CACHE_DIRS );
}



/**
 * Recursive directory creation based on full path.
 *
 * @source wp_mkdir_p() in /wp-includes/functions.php
 * @since 1.3.4
 */

function rocket_mkdir_p( $target )
{

	// from php.net/mkdir user contributed notes
	$target = str_replace( '//', '/', $target );

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = rtrim($target, '/'); // Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
	if ( empty($target) )
		$target = '/';

	if ( file_exists( $target ) )
		return @is_dir( $target );

	// Attempting to create the directory may clutter up our display.
	if ( rocket_mkdir( $target ) ) {
		return true;
	} elseif ( is_dir( dirname( $target ) ) ) {
			return false;
	}

	// If the above failed, attempt to create the parent node, then try again.
	if ( ( $target != '/' ) && ( rocket_mkdir_p( dirname( $target ) ) ) )
		return rocket_mkdir_p( $target );

	return false;
}



/**
 * File creation based on WordPress Filesystem
 *
 * @since 1.3.5
 *
 */

function rocket_put_content( $file, $content )
{

	global $wp_filesystem;
    if( !$wp_filesystem )
    {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		$wp_filesystem = new WP_Filesystem_Direct( new StdClass() );
	}
	return $wp_filesystem->put_contents( $file, $content, 0644 );
}