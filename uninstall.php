<?php

// If uninstall not called from WordPress exit
defined( 'WP_UNINSTALL_PLUGIN' ) or die( 'Cheatin&#8217; uh?' );

// Delete all transients
delete_site_transient( 'update_wprocket' );
delete_site_transient( 'update_wprocket_response' );
delete_transient( 'wp_rocket_settings' );
delete_transient( 'rocket_check_licence_30' );
delete_transient( 'rocket_check_licence_1' );
delete_transient( 'rocket_cloudflare_ips' );

// Delete WP Rocket options
delete_option( 'wp_rocket_settings' );

// Delete all user meta related to WP Rocket
delete_metadata( 'user', '', 'rocket_boxes', '', true );

// Clear scheduled WP Rocket Cron
wp_clear_scheduled_hook( 'rocket_purge_time_event' );
wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );

/**
 * Remove all cache files
 *
 * @since 1.2.0
 *
 * @param string $dir Directory path to remove.
 */
function __rocket_rrmdir( $dir ) {

	if ( ! is_dir( $dir ) ) {
		@unlink( $dir );
		return;	
	}

    if ( $globs = glob( $dir . '/*', GLOB_NOSORT ) ) {
	    foreach ( $globs as $file ) {
			is_dir( $file ) ? __rocket_rrmdir($file) : @unlink( $file );
	    }
	}

    @rmdir($dir);

}

__rocket_rrmdir( WP_CONTENT_DIR . '/cache/wp-rocket/' );
__rocket_rrmdir( WP_CONTENT_DIR . '/cache/min/' );
__rocket_rrmdir( WP_CONTENT_DIR . '/cache/busting/' );
__rocket_rrmdir( WP_CONTENT_DIR . '/wp-rocket-config/' );