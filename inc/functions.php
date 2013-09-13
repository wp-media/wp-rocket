<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Get all pages we don't cache (string)
 * This function is used in inc/front/process.php and in inc/front/htaccess.php
 *
 * since 1.0
 *
 */

function get_rocket_pages_not_cached()
{
	global $wp_rewrite;
	$pages = array( '.*/' . $wp_rewrite->feed_base . '/' );
	$cache_reject_uri = get_rocket_option( 'cache_reject_uri', array() );
	if( count( $cache_reject_uri ) )
		$pages =  array_filter( array_merge( $pages, (array)$cache_reject_uri ) );

	return implode( '|', $pages );
}



/**
 * Get all cookie names we don't cache (string)
 * This function is used in inc/front/process.php and in inc/front/htaccess.php
 *
 * since 1.0
 *
 */

function get_rocket_cookies_not_cached()
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
		$cookies =  array_filter( array_merge( $cookies, (array)$cache_reject_cookies ) );

	return implode( '|', $cookies );
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
 * Delete one or several cache files
 *
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

		if( $url )
			rocket_rrmdir( WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( $url ) );

		do_action( 'after_rocket_clean_file', $url );

	}
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
		get_year_link( $date[0] ) . 'index.html',
		get_year_link( $date[0] ) . $wp_rewrite->pagination_base,
		get_month_link( $date[0], $date[1] ) . 'index.html',
		get_month_link( $date[0], $date[1] ) . $wp_rewrite->pagination_base,
		get_day_link( $date[0], $date[1], $date[2] )
	);

    return $urls;
}



/**
 * Remove the home cache file and pagination
 *
 * @since 1.0
 *
 */

function rocket_clean_home()
{
	$root = WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( home_url() );
	$root = apply_filters( 'before_rocket_clean_home', $root );

	@unlink( $root . '/index.html' );
    rocket_rrmdir( $root . '/' . $GLOBALS['wp_rewrite']->pagination_base );

    do_action( 'after_rocket_clean_home', $root );
}



/**
 * Remove all cache files of the domain
 *
 * @since 1.0
 *
 */

function rocket_clean_domain()
{
	$domain = apply_filters( 'rocket_clean_domain', WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( home_url() ) );

	do_action( 'before_rocket_clean_domain', $domain );

	// Delete cache domain files
    rocket_rrmdir( $domain );

    do_action( 'after_rocket_clean_domain', $domain );
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
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 */

function rocket_rrmdir( $dir, $dirs_to_preserve = array() )
{

	$dir = apply_filters( 'before_rocket_rrmdir', $dir, $dirs_to_preserve );

	if( !is_dir( $dir ) ) :
		@unlink( $dir );
		return;
	endif;

	$dir = rtrim( $dir, '/' );
    if( $globs = glob( $dir . '/*' ) ) {

		foreach( $globs as $file ) {

			if( is_dir( $file ) ) {
				if( !in_array( str_replace( WP_ROCKET_CACHE_PATH, '', $file ), $dirs_to_preserve ) )
					rocket_rrmdir( $file, $dirs_to_preserve );
			}
			else {
			   @unlink( $file );
			}

		}

	}

	@rmdir($dir);

	do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve );
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
 * Get the interval task cron purge in seconds
 * This setting can be changed from the options page of the plugin
 *
 * @since 1.0
 *
 */

function get_rocket_cron_interval()
{
	if( !get_rocket_option( 'purge_cron_interval' ) || !get_rocket_option( 'purge_cron_unit' ) )
		return 0;
	return (int)( get_rocket_option( 'purge_cron_interval' ) * constant( get_rocket_option( 'purge_cron_unit' ) ) );
}



/**
 * A wrapper to easily get rocket option
 *
 * @since 1.3.0
 *
 */

function get_rocket_option( $option, $default=false )
{
	$options = get_option( WP_ROCKET_SLUG );
	return isset( $options[$option] ) ? $options[$option] : $default;
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
 * Renew all boxes for everyone if $uid is missing
 *
 * @param integer $uid
 *
 * @since 1.1.10
 *
 */

function rocket_renew_all_boxes( $uid=0, $keep_this=array() )
{
	if( (int)$uid>0 ){
		delete_user_meta( $uid, 'rocket_boxes' );
	}else{
		global $wpdb;
		$query = 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key="rocket_boxes"';
		// do not use $wpdb->delete because WP 3.4 is required!
		$wpdb->query( $query );
	}
	// $keep_this works only for the current user
	if( !empty( $keep_this ) ) {
		if( is_array( $keep_this ) ) {
			foreach( $keep_this as $kt ) {
				rocket_dismiss_box( $kt );
			}
		}else{
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
	if( $actual ) {
		unset( $actual[array_search( $function, $actual )] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}
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

function rocket_is_plugin_active_for_network( $plugin ) {
	if ( !is_multisite() )
		return false;

	$plugins = get_site_option( 'active_sitewide_plugins');
	if ( isset($plugins[$plugin]) )
		return true;

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
 * @source : wp-admin/includes/post.php
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


function get_rocket_request_uri() 
{
	
	if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) 
	{
		global $q_config;
		return $q_config['url_info']['original_url'];
	}
	
	return $_SERVER['REQUEST_URI'];
}