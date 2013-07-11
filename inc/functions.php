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
	$options = get_option( 'wp_rocket_settings' );
	$pages = array( '.*/feed/' );

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
	$cookies = array( str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE ), 'wp-postpass_', 'wptouch_switch_toggle' );

	if( get_option( 'comment_moderation' ) == '1' || get_option( 'comment_whitelist' ) == '1' )
		$cookies[] = 'comment_author_';

	if( isset( $options['cache_reject_uri'] ) && count( $options['cache_reject_cookies'] ) >= 1 )
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

		if( $url )
			rocket_rrmdir( WP_ROCKET_CACHE_PATH . str_replace( array( 'http://', 'https://' ), '', $url ) );

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
	// Get the day and month of the post
	$date = explode( '-', get_the_time( 'Y-m-d', $post_ID ) );
	global $wp_rewrite;
	$urls = array(
		get_year_link( $date[0] ) . 'index.html',
		get_year_link( $date[0] ) . $wp_rewrite->pagination_base,
		get_month_link( $date[0], $date[1] ),
		get_month_link( $date[0], $date[1] ) . $wp_rewrite->pagination_base,
		get_day_link( $date[0], $date[1], $date[2] ),
		get_day_link( $date[0], $date[1], $date[2] ) . $wp_rewrite->pagination_base
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
	$root = WP_ROCKET_CACHE_PATH . str_replace( array( 'http://', 'https://' ), '', home_url( '/' ) );

	$root = apply_filters( 'before_rocket_clean_home', $root );

	@unlink( $root . '/index.html' );
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
	do_action( 'before_rocket_clean_domain' );
	
	// Delete cache domaine files
    rocket_rrmdir( WP_ROCKET_CACHE_PATH . str_replace( array( 'http://', 'https://' ), '', home_url( '/' ) ) );

	// Run WP Rocket Bot for preload cache files
	run_rocket_bot( 'cache-preload' );

    do_action( 'after_rocket_clean_domain' );
}



/**
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 */
function rocket_rrmdir( $dir )
{
	do_action( 'before_rocket_rrmdir', $dir );
	if( !is_dir( $dir ) ):
		@unlink( $dir );
		return;
	endif;
	
    if( $globs = glob( $dir . '/*' ) ) {
	    
	    foreach( $globs as $file )
	        is_dir( $file ) ? rocket_rrmdir($file) : @unlink( $file );	
	}

    @rmdir($dir);
	do_action( 'after_rocket_rrmdir', $dir );
}



/**
 * Count cached files
 *
 * since 1.0
 *
 */

function rocket_count_cache_contents( $base = null )
{
	do_action( 'before_rocket_count_cache_contents', $base );
    $base = is_null( $base ) ? WP_ROCKET_CACHE_PATH : $base;

    if( !file_exists( $base ) )
    	return 0;

    $count = 0;

    $root = scandir( $base );

    foreach( $root as $value )
    {
        if( $value=='.' || $value=='..' )
			continue;

        if( is_file( $base.'/'.$value ) )
			$count++;
		else
			$count = $count + rocket_count_cache_contents( $base.'/'.$value );
    }

    return $count;
}



/**
 * Get relative url
 * Clean URL file to get only the equivalent of REQUEST_URI
 * ex: rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/') return /referencement-wordpress/
 *
 * since 1.0
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
 * since 1.0
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
 * since 1.0
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
 * since 1.0
 *
 */

function get_rocket_option( $option, $default=false )
{
// soon
}



/**
 * Returns a full and correct home_url without subdmain, see rocket_get_domain()
 *
 * since 1.0
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
 * source : http://stackoverflow.com/a/15498686
 * since 1.0
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
 * @param mixed $spider
 *
 * since 1.1.0 Remove $start_url arg. This is a variable
 * since 1.0
 *
 */

function run_rocket_bot( $spider = 'cache-preload' )
{
	
	$start_url = '';
	
	if( $spider == 'cache-preload' )
		$start_url = home_url();
	else if( $spider == 'cache-json' )
		$start_url = WP_ROCKET_URL . 'cache.json';
	
	if( empty($start_url) )
		return false;
	
	do_action( 'before_run_rocket_bot' );
	
	wp_remote_get( WP_ROCKET_BOT_URL.'?spider=' . $spider . '&start_url=' . $start_url );

	do_action( 'after_run_rocket_bot' );

}