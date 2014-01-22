<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );

/*
 * Deprecated functions come here to die.
 */
 
 
if( !function_exists( 'get_rocket_pages_not_cached' ) ) :
/**
 * Get all pages we don't cache (string)
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_cache_reject_uri()
 *
 */	
function get_rocket_pages_not_cached() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_cache_reject_uri()" );
	return get_rocket_cache_reject_uri();
}
endif;

if( !function_exists( 'get_rocket_cookies_not_cached' ) ) :
/**
 * Get all cookie names we don't cache (string)
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_cache_reject_cookies()
 *
 */	
function get_rocket_cookies_not_cached() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_cache_reject_cookies()" );
	return get_rocket_cache_reject_cookies();
}
endif;

if( !function_exists( 'get_rocket_cron_interval' ) ) :
/**
 * Get the interval task cron purge in seconds
 * This setting can be changed from the options page of the plugin
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_purge_cron_interval()
 *
 */	
function get_rocket_cron_interval() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_purge_cron_interval()" );
	return get_rocket_purge_cron_interval();
}
endif;