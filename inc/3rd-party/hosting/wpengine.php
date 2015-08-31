<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WpeCommon' ) && function_exists( 'wpe_param' ) ) :

/**
 * Conflict with WP Engine caching system
 *
 * @since 2.6.4
 */
add_action( 'init', '__rocket_stop_generate_caching_files_on_wpengine' );
function __rocket_stop_generate_caching_files_on_wpengine() {
	add_filter( 'do_rocket_generate_caching_files', '__return_false' );
}

/**
 * Run WP Rocket preload bot after purged the Varnish cache via WP Engine Hosting
 *
 * @since 2.6.4
 *
 * @return void
 */
add_action( 'admin_init', '__rocket_run_rocket_bot_after_wpengine' );
function __rocket_run_rocket_bot_after_wpengine() {
	if ( wpe_param( 'purge-all' ) && defined( 'PWP_NAME' ) && check_admin_referer( PWP_NAME . '-config' ) ) {
		// Preload cache
		run_rocket_bot( 'cache-preload' );
	}
}

/* @since 2.6.4
 * For not conflit with WP Engine
*/
add_action( 'after_rocket_clean_domain', 'rocket_clean_wpengine' );

/**
 * Call the cache server to purge the cache with WP Engine hosting.
 *
 * @since 2.6.4
 *
 * @return void
 */
function rocket_clean_wpengine() {	
	if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
		WpeCommon::purge_memcached();
	}
	
	if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
		WpeCommon::purge_varnish_cache();
	}
}

endif;