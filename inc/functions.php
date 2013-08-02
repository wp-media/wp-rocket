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
	$options = get_option( 'wp_rocket_settings' );
	$pages = array( '.*/' . $wp_rewrite->feed_base . '/' );

	if( isset( $options['cache_reject_uri'] ) && count( $options['cache_reject_uri'] ) >= 1 )
		$pages =  array_filter( array_merge( $pages, (array)$options['cache_reject_uri'] ) );

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
	$options = get_option( 'wp_rocket_settings' );
	$cookies = array(
		str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE ),
		'wp-postpass_',
		'wptouch_switch_toggle',
		'comment_author_',
		'comment_author_email_'
	);

	if( isset( $options['cache_reject_cookies'] ) && count( $options['cache_reject_cookies'] ) )
		$cookies =  array_filter( array_merge( $cookies, (array)$options['cache_reject_cookies'] ) );

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
	$options = get_option( 'wp_rocket_settings' );
	return isset( $options['cache_mobile'] ) && $options['cache_mobile'] == '1';
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
			rocket_rrmdir( WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol($url) );

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
	$root = WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( home_url( '/' ) );
	$root = apply_filters( 'before_rocket_clean_home', $root );

	@unlink( $root . 'index.html' );
    rocket_rrmdir( $root . $GLOBALS['wp_rewrite']->pagination_base );

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
	$domain = WP_ROCKET_CACHE_PATH . parse_url( home_url(), PHP_URL_HOST );
		
	do_action( 'before_rocket_clean_domain', $domain );
	
	// Delete cache domain files
    rocket_rrmdir( $domain );

    do_action( 'after_rocket_clean_domain', $domain );
}



/**
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 */
 
function rocket_rrmdir( $dir, $dirs_to_preserve = array() )
{
	
	do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve );

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
 *
 */

function rocket_clean_exclude_file( $file )
{
	$file = str_replace( array( '#\?.*$#', home_url(), 'http://', 'https://' ), '', $file );
	$ex = explode( '?', $file );
	$r = reset( $ex );
    return trim( $r );
}



/**
 * Determine if the key is valid
 *
 * @since 1.0
 *
 */

function rocket_valid_key()
{
	$options = get_option( WP_ROCKET_SLUG );
	if( !isset( $options['consumer_key'] ) || !isset( $options['secret_key'] ) )
		return false;
	return $options['consumer_key']==hash( 'crc32', rocket_get_domain( home_url() ) ) && $options['secret_key']==md5( $options['consumer_key'] );
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
	$options = get_option( WP_ROCKET_SLUG );
	if( !isset( $options['purge_cron_interval'] ) || !isset( $options['purge_cron_unit'] ) )
		return 0;
	return (int)( $options['purge_cron_interval'] * constant( $options['purge_cron_unit'] ) );
}



/**
 * TO DO - Description
 *
 * @since 1.0
 *
 */

function get_rocket_option( $option, $default=false )
{
// soon
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

function rocket_renew_all_boxes( $uid=0 )
{
	if( (int)$uid>0 ){
		delete_user_meta( $uid, 'rocket_boxes' );
	}else{
		global $wpdb;
		// do not use $wpdb->delete because WP 3.4 is required!
		$wpdb->query( 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key="rocket_boxes"' );
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
 
function rocket_remove_url_protocol( $url ) 
{
	return str_replace( array( 'http://', 'https://' ) , '', $url );
}