<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

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
		run_rocket_bot( 'cache-preload' );
	}
}