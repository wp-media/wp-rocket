<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'GD_System_Plugin_Cache_Purge' ) ) :

/**
 * Clear WP Rocket cache after purged the Varnish cache via GoDaddy Hosting
 *
 * @since 2.6.5
 *
 * @return void
 */
add_action( 'init', '__rocket_clear_cache_after_godaddy', 0 );
function __rocket_clear_cache_after_godaddy() {
	if ( ! isset( $_REQUEST['GD_COMMAND'] ) || $_REQUEST['GD_COMMAND'] != 'FLUSH_CACHE' ) {
		return;
	}
	
	if ( wp_verify_nonce( $_REQUEST['GD_NONCE'], 'GD_FLUSH_CACHE' ) ) {
		// Clear all caching files
		rocket_clean_domain();
		
		// Preload cache
		run_rocket_preload_cache( 'cache-preload' );
	}
}

/* @since 2.6.5
 * For not conflit with GoDaddy
*/
add_action( 'after_rocket_clean_domain', 'rocket_clean_godaddy' );

/**
 * Call the cache server to purge the cache with GoDaddy hosting.
 *
 * @since 2.6.5
 *
 * @return void
 */
function rocket_clean_godaddy() {	
	global $gd_cache_purge;
	
	if( is_a( $gd_cache_purge, 'GD_System_Plugin_Cache_Purge' ) && method_exists( 'GD_System_Plugin_Cache_Purge', 'ban_cache' ) ) {
		$gd_cache_purge->ban_cache();	
	}
}

endif;