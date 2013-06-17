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
 * TO DO - Description
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
 * TO DO - Description
 *
 * since 1.0
 *
 */
function is_rocket_cache_mobile()
{
	$options = get_option( 'wp_rocket_settings' );
	return isset( $options['cache_mobile'] ) && $options['cache_mobile'] == '1' ? true : false;
}



/**
 * Remove cache files
 *
 * @since 1.0
 *
 */
function rocket_clean_files( $urls )
{

	if( is_string( $urls ) )
		$urls = (array)$urls;

    foreach( array_filter($urls) as $url )
    {

		$url = apply_filters( 'before_rocket_clean_file', $url );

		if( $url )
			rocket_rrmdir( WP_ROCKET_CACHE_PATH . str_replace( array( 'http://', 'https://' ), '', $url ) );

		do_action( 'after_rocket_clean_file', $url );

	}
}



/**
 * Remove all terms cache files of a specific post
 *
 * @since 1.0
 *
 */
function rocket_clean_post_terms( $post_ID )
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

	do_action( 'before_rocket_clean_post_terms', $urls, $post_ID );

    rocket_clean_files( $urls );

    do_action( 'after_rocket_clean_post_terms', $urls, $post_ID );
}



/**
 *
 *
 * @since 1.0
 *
 */
function rocket_clean_post_dates( $post_ID )
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

	do_action( 'before_rocket_clean_post_dates', $urls, $post_ID );

	rocket_clean_files( $urls );

	do_action( 'after_rocket_clean_post_dates', $urls, $post_ID );
}



/**
 * Remove the home cache file and pagination
 *
 * @since 1.0
 *
 */
function rocket_clean_home()
{

	$root = WP_ROCKET_CACHE_PATH . str_replace( 'http://', '', home_url( '/' ) );

	do_action( 'before_rocket_clean_home' );

	@unlink( $root . '/index.html' );
    rocket_rrmdir( $root . $GLOBALS['wp_rewrite']->pagination_base );

    do_action( 'after_rocket_clean_home' );
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

    rocket_rrmdir( WP_ROCKET_CACHE_PATH . str_replace( 'http://', '', home_url( '/' ) ) );

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

	if( !is_dir( $dir ) ):
		@unlink( $dir );
		return;
	endif;

    foreach( glob( $dir . '/*' ) as $file )
        is_dir( $file ) ? rocket_rrmdir($file) : @unlink( $file );

    @rmdir($dir);

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_count_cache_contents( $base = null )
{
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



function rocket_clean_exclude_file( $file )
{

	// Get relative url
    return trim( reset( explode( '?', str_replace( array( '#\?.*$#', home_url( '/' ), 'http://', 'https://' ), '', $file ) ) ) );

}

function rocket_valid_key()
{
	$options = get_option( WP_ROCKET_SLUG );
	if( !isset( $options['consumer_key'] ) || !isset( $options['secret_key'] ) )
		return false;
	return $options['consumer_key']==hash( 'crc32', get_rocket_home_url().chr(98) ) && $options['secret_key']==md5( $options['consumer_key'] );
}

function get_rocket_cron_interval()
{
	$options = get_option( WP_ROCKET_SLUG );
	if( !isset( $options['purge_cron_interval'] ) || !isset( $options['purge_cron_unit'] ) )
		return 0;
	return (int)( $options['purge_cron_interval'] * constant( $options['purge_cron_unit'] ) );
}

function get_rocket_home_url()
{
	return apply_filters( 'rocket_home_url', str_replace( 'www.', '', home_url('/') ) );
}
