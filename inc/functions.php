<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Generate the content of advanced-cache.php file
 *
 * @since 2.0
 *
 */

function rocket_generate_advanced_cache_file()
{

	$buffer = '<?php' . "\n";
	$buffer .= 'defined( \'ABSPATH\' ) or die( \'Cheatin\\\' uh?\' );' . "\n\n";

	// Get cache path
	$buffer .= '$rocket_cache_path = \'' . WP_ROCKET_CACHE_PATH . '\'' . ";\n";

	// Get config path
	$buffer .= '$rocket_config_path = \'' . WP_ROCKET_CONFIG_PATH . '\'' . ";\n";

	// Include the process file in buffer
	$buffer .= 'include( \''. WP_ROCKET_FRONT_PATH . 'process.php' . '\' );';

	// Create advanced-cache.php file
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', $buffer );
}



/**
 * TO DO
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
 * TO DO
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
 * A wrapper to easily get rocket option
 *
 * @since 2.0 Use get_site_option "à la place de" get_option
 * @since 1.3.0
 *
 */

function get_rocket_option( $option, $default=false )
{
	$options = get_option( WP_ROCKET_SLUG );
	return isset( $options[$option] ) ? $options[$option] : $default;
}



/**
 * Check if we need to cache the mobile version of the website (if available)
 *
 * since 1.0
 *
 */

function is_rocket_cache_mobile()
{
	return get_rocket_option( 'cache_mobile', 0 );
}



/**
 * Check if we need to cache SSL requests of the website (if available)
 *
 * since 1.0
 *
 */

function is_rocket_cache_ssl()
{
	return get_rocket_option( 'cache_ssl', 0 );
}



/**
 * Get the interval task cron purge in seconds
 * This setting can be changed from the options page of the plugin
 *
 * @since 1.0
 *
 */

function get_rocket_purge_cron_interval()
{
	if( !get_rocket_option( 'purge_cron_interval' ) || !get_rocket_option( 'purge_cron_unit' ) )
		return 0;
	return (int)( get_rocket_option( 'purge_cron_interval' ) * constant( get_rocket_option( 'purge_cron_unit' ) ) );
}



/**
 * Get all pages we don't cache (string)
 *
 * @since 2.0
 *
 */

function get_rocket_cache_reject_uri()
{
	global $wp_rewrite;
	$pages = array( '.*/' . $wp_rewrite->feed_base . '/' );
	$cache_reject_uri = get_rocket_option( 'cache_reject_uri', array() );

	if( count( $cache_reject_uri ) )
		$pages =  array_filter( array_merge( $pages, $cache_reject_uri ) );

	return implode( '|', $pages );
}



/**
 * Get all cookie names we don't cache (string)
 *
 * since 1.0
 *
 */

function get_rocket_cache_reject_cookies()
{
	$cookies = array(
		str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE ),
		'wp-postpass_',
		'wptouch_switch_toggle',
		'comment_author_',
		'comment_author_email_'
	);
	$cache_reject_cookies = get_rocket_option( 'cache_reject_cookies', array() );

	if( count( $cache_reject_cookies ) )
		$cookies =  array_filter( array_merge( $cookies, $cache_reject_cookies ) );

	return implode( '|', $cookies );
}



/**
 * Get all terms archives urls associated to a specific post
 *
 * @since 1.0
 *
 */

function get_rocket_post_terms_urls( $post_ID )
{
	$urls = array();

	foreach ( get_object_taxonomies( get_post_type( $post_ID ) ) as $taxonomy )
	{

		// Get the terms related to post
		$terms = get_the_terms( $post_ID, $taxonomy );

		if ( !empty( $terms ) )
		{
			foreach ( $terms as $term )
				$urls[] = get_term_link( $term->slug, $taxonomy );
		}

	}
	return apply_filters( 'get_rocket_post_terms_urls', $urls );
}



/**
 * Get all dates archives urls associated to a specific post
 *
 * @since 1.0
 *
 */

function get_rocket_post_dates_urls( $post_ID )
{
	global $wp_rewrite;

	// Get the day and month of the post
	$date = explode( '-', get_the_time( 'Y-m-d', $post_ID ) );

	$urls = array(
		get_year_link ( $date[0] ) . 'index.html',
		get_year_link ( $date[0] ) . $wp_rewrite->pagination_base,
		get_month_link( $date[0], $date[1] ) . 'index.html',
		get_month_link( $date[0], $date[1] ) . $wp_rewrite->pagination_base,
		get_day_link  ( $date[0], $date[1], $date[2] )
	);

    return $urls;
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



/**
 * Get relative url
 * Clean URL file to get only the equivalent of REQUEST_URI
 * ex: rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/') return /referencement-wordpress/
 *
 * @since 1.0
 * @since 1.3.5 Redo the function
 *
 */

function rocket_clean_exclude_file( $file )
{
	if( !$file )
		return false;
	$path = parse_url( $file, PHP_URL_PATH );
    return $path;
}



/**
 * Determine if the key is valid
 *
 * @since 1.0
 *
 */

function rocket_valid_key()
{

	if( !get_rocket_option( 'consumer_key' ) || !get_rocket_option( 'secret_key' ) )
		return false;

	return get_rocket_option( 'consumer_key' )==hash( 'crc32', rocket_get_domain( home_url() ) ) && get_rocket_option( 'secret_key' )==md5( get_rocket_option( 'consumer_key' ) );

}



/**
 * Returns a full and correct home_url without subdmain, see rocket_get_domain()
 *
 * @since 1.0
 *
 */

function get_rocket_home_url( $url=null )
{
	$url = is_null( $url ) ? home_url( '/' ) : $url;
	$s = is_ssl() ? 's' : '';
	return 'http' . $s . '://' . rocket_get_domain( $url );
}



/**
 * Get the domain of an URL without subdomain
 * (ex: rocket_get_domain( 'http://www.geekpress.fr' ) return geekpress.fr
 *
 * @source : http://stackoverflow.com/a/15498686
 * @since 1.0
 *
 */

function rocket_get_domain( $url )
{
      $urlobj = parse_url( $url );
      $domain = $urlobj['host'];
      if( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) )
          return $regs['domain'].chr(98); //// beta
      return false;
}



/**
 * Launch the Robot
 *
 * @since 1.0
 *
 */

function run_rocket_bot( $spider = 'cache-preload', $start_url = '' )
{

	if( $spider == 'cache-preload' && empty( $start_url ) )
		$start_url = home_url();
	else if( $spider == 'cache-json' )
		$start_url = WP_ROCKET_URL . 'cache.json';

	if( empty( $start_url ) )
		return false;

	do_action( 'before_run_rocket_bot' );

	wp_remote_get( WP_ROCKET_BOT_URL.'?spider=' . $spider . '&start_url=' . $start_url );

	do_action( 'after_run_rocket_bot' );
}



/**
 * Launch the Cache Preload Robot for all active languages
 *
 * @since 2.0
 *
 */

function run_rocket_bot_for_selected_lang( $lang )
{

	// Check if WPML is activated
	if( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') )
	{

		global $sitepress;
		$url = $sitepress->language_url( $lang );
	}
	else if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
	{
		$url = qtrans_convertURL( home_url(), $lang, true );
	}

	run_rocket_bot( 'cache-preload', $url );
}



/**
 * Launch the Cache Preload Robot for all active languages
 *
 * @since 2.0
 *
 */

function run_rocket_bot_for_all_langs()
{

	$langs = get_rocket_all_active_langs_uri();
	foreach ( $langs as $lang )
		run_rocket_bot( 'cache-preload', $lang );
}



/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 * @source : wp-admin/includes/plugin.php
 *
 */

function rocket_is_plugin_active( $plugin )
{
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || rocket_is_plugin_active_for_network( $plugin );
}



/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 * @source : wp-admin/includes/plugin.php
 *
 */

function rocket_is_plugin_active_for_network( $plugin )
{
	if ( !is_multisite() )
		return false;

	$plugins = get_site_option( 'active_sitewide_plugins');
	if ( isset($plugins[$plugin]) )
		return true;

	return false;
}



/**
 * Check if a translation plugin is activated (WPML or qTranslate)
 *
 * @since 2.0
 *
 */

function rocket_has_translation_plugin_active()
{

	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) // WPML
		|| rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) // qTranslate
	{
		return true;
	}

	return false;
}



/**
 * Get an url without protocol
 *
 * @since 1.3.0
 *
 */

function rocket_remove_url_protocol( $url, $no_dots=false )
{
	$url = str_replace( array( 'http://', 'https://' ) , '', $url );
	if( apply_filters( 'rocket_url_no_dots', $no_dots ) )
		$url = str_replace( '.', '_', $url );
	return $url;
}



/**
 * Get the permalink post
 *
 * @since 1.3.1
 * @source : get_sample_permalink() in wp-admin/includes/post.php
 *
 */

function get_rocket_sample_permalink( $id )
{
	$post = get_post($id);
	if ( !$post->ID )
		return array('', '');

	$ptype = get_post_type_object($post->post_type);

	$original_status = $post->post_status;
	$original_date = $post->post_date;
	$original_name = $post->post_name;

	// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
	if ( in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
		$post->post_status = 'publish';
		$post->post_name = sanitize_title($post->post_name ? $post->post_name : $post->post_title, $post->ID);
	}

	$post->post_name = wp_unique_post_slug($post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent);

	$post->filter = 'sample';

	$permalink = get_permalink($post, true);

	// Replace custom post_type Token with generic pagename token for ease of use.
	$permalink = str_replace("%$post->post_type%", '%pagename%', $permalink);

	// Handle page hierarchy
	if ( $ptype->hierarchical ) {
		$uri = get_page_uri($post);
		$uri = untrailingslashit($uri);
		$uri = strrev( stristr( strrev( $uri ), '/' ) );
		$uri = untrailingslashit($uri);
		$uri = apply_filters( 'editable_slug', $uri );
		if ( !empty($uri) )
			$uri .= '/';
		$permalink = str_replace('%pagename%', "{$uri}%pagename%", $permalink);
	}

	$permalink = array($permalink, apply_filters('editable_slug', $post->post_name));
	$post->post_status = $original_status;
	$post->post_date = $original_date;
	$post->post_name = $original_name;
	unset($post->filter);

	return $permalink;
}



/**
 * Extract and return host, path and scheme of an URL
 *
 * @since 2.0
 *
 */

function get_rocket_parse_url( $url )
{

	$url    = parse_url( $url );
	$host   = $url['host'];
	$path   = isset( $url['path'] ) ? $url['path'] : '';
	$scheme = isset( $url['scheme'] ) ? $url['scheme'] : '';
	return array( $host, $path, $scheme );
}



/**
 * Extract and return host, path and scheme for a specific lang
 *
 * @since 2.0
 *
 */

function get_rocket_parse_url_for_lang( $lang )
{

	// WPML
	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{
		global $sitepress;
		return get_rocket_parse_url( $sitepress->language_url( $lang ) );
	}

	// qTranslate
	if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
		return get_rocket_parse_url( qtrans_convertURL( home_url(), $lang, true ) );
}



/**
 * Get infos of all active languages
 *
 * @since 2.0
 *
 */

function get_rocket_all_active_langs()
{

	// WPML
	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{
		global $sitepress;
		return $sitepress->get_active_languages();
	}

	// qTranslate
	if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
	{
		global $q_config;
		return $q_config['enabled_languages'];
	}

	return false;
	
}



/**
 * Get URI all of active languages
 *
 * @since 2.0
 *
 */

function get_rocket_all_active_langs_uri()
{

	$urls  = array();
	$langs = get_rocket_all_active_langs();

	// WPML
	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{

		global $sitepress;
		foreach ( array_keys( $langs ) as $lang )
			$urls[] = $sitepress->language_url( $lang );

	}
	// qTranslate
	else if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
	{

		foreach ( $langs as $lang )
			$urls[] = qtrans_convertURL( home_url(), $lang, true );

	}

	return $urls;
}



/**
 * TO DO
 *
 * @since 2.0
 *
 */

function get_rocket_langs_to_preserve( $current_lang )
{

	$langs = get_rocket_all_active_langs();
	$langs_to_preserve = array();

	// Unset current lang to the preserve dirs

	// WPML
	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{
		unset( $langs[$current_lang] );
		$langs = array_keys( $langs );
	}

	// qTranslate
	if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
	{
		$langs = array_flip( $langs );
		unset( $langs[$current_lang] );
		$langs = array_flip( $langs );
	}

	// Stock all URLs of langs to preserve
	foreach ( $langs as $lang )
	{
		list( $host, $path ) = get_rocket_parse_url_for_lang( $lang );
		$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $host . '(.*)/' . trim( $path, '/' );
	}

	$langs_to_preserve = apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
	return $langs_to_preserve;
}



/**
 * Renew all boxes for everyone if $uid is missing
 *
 * @param integer $uid
 *
 * @since 1.1.10
 *
 */

function rocket_renew_all_boxes( $uid=0, $keep_this=array() )
{
	if( (int)$uid>0 )
	{
		delete_user_meta( $uid, 'rocket_boxes' );
	}
	else
	{
		global $wpdb;
		$query = 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key="rocket_boxes"';
		// do not use $wpdb->delete because WP 3.4 is required!
		$wpdb->query( $query );
	}

	// $keep_this works only for the current user
	if( !empty( $keep_this ) )
	{
		if( is_array( $keep_this ) )
		{
			foreach( $keep_this as $kt )
			{
				rocket_dismiss_box( $kt );
			}
		}
		else
		{
			rocket_dismiss_box( $keep_this );
		}
	}
}



/**
 * Renew a dismissed error box admin side
 *
 * @param string $function
 * @param integer $uid
 *
 * @since 1.1.10
 *
 */

function rocket_renew_box( $function, $uid=0 )
{
	global $current_user;
	$uid = $uid==0 ? $current_user->ID : $uid;
	$actual = get_user_meta( $uid, 'rocket_boxes', true );
	if( $actual )
	{
		unset( $actual[array_search( $function, $actual )] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}
}