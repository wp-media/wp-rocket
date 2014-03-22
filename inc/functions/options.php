<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


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
 * Get all uri we don't cache
 *
 * @since 2.0
 *
 */

function get_rocket_cache_reject_uri()
{

	$uri = get_rocket_option( 'cache_reject_uri', array() );
	$uri[] = '.*/' . $GLOBALS['wp_rewrite']->feed_base . '/';

	/**
	 * Filter the rejected uri
	 *
	 * @param array $uri
	 * @since 2.1
	*/
	$uri = apply_filters( 'rocket_cache_reject_uri', $uri );

	$uri = implode( '|', array_filter( $uri ) );
	return $uri;

}



/**
 * Get all cookie names we don't cache
 *
 * @since 2.0
 *
 */

function get_rocket_cache_reject_cookies()
{
	
	$cookies   = get_rocket_option( 'cache_reject_cookies', array() );
	$cookies[] = str_replace( COOKIEHASH, '', LOGGED_IN_COOKIE );
	$cookies[] = 'wp-postpass_';
	$cookies[] = 'wptouch_switch_toggle';
	$cookies[] = 'comment_author_';
	$cookies[] = 'comment_author_email_';

	/**
	 * Filter the rejected cookies
	 *
	 * @param array $cookies
	 * @since 2.1
	*/
	$cookies = apply_filters( 'rocket_cache_reject_cookies', $cookies );
	
	$cookies = implode( '|', array_filter( $cookies ) );
	return $cookies;

}



/*
 * TO DO
 *
 * @since 2.1
 *
 */

function get_rocket_cdn_cnames( $_zone = 'all' )
{

	if( (int)get_rocket_option( 'cdn' ) == 0 ) 
	{
		return array();	
	}

	$hosts       = array();
	$cnames      = get_rocket_option( 'cdn_cnames', array() );
	$cnames_zone = get_rocket_option( 'cdn_zone', array() );
	$_zone 		 = is_array( $_zone ) ? $_zone : (array)$_zone;

	//
	foreach( $cnames as $k=>$_urls )
	{

		//
		if( in_array( $cnames_zone[$k], $_zone ) )
		{

			$_urls = explode( ',' , $_urls );
			$_urls = array_map( 'trim' , $_urls );

			//
			foreach( $_urls as $url ) 
			{
				$hosts[] = $url;
			}

		}

	}

	return $hosts;

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