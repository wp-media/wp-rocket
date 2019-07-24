<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || die( 'Cheatin&#8217; uh?' );

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

// Delete all transients.
delete_site_transient( 'wp_rocket_update_data' );
delete_transient( 'wp_rocket_settings' );
delete_transient( 'rocket_cloudflare_ips' );
delete_transient( 'rocket_send_analytics_data' );

// Delete WP Rocket options.
delete_option( 'wp_rocket_settings' );
delete_option( 'rocket_analytics_notice_displayed' );

// Delete Compatibility options.
delete_option( 'rocket_jetpack_eu_cookie_widget' );

// Delete all user meta related to WP Rocket.
delete_metadata( 'user', '', 'rocket_boxes', '', true );

// Clear scheduled WP Rocket Cron.
wp_clear_scheduled_hook( 'rocket_purge_time_event' );
wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );
wp_clear_scheduled_hook( 'rocket_google_tracking_cache_update' );
wp_clear_scheduled_hook( 'rocket_facebook_tracking_cache_update' );
wp_clear_scheduled_hook( 'rocket_cache_dir_size_check' );

/**
 * Remove all cache files.
 *
 * @since 1.2.0
 *
 * @param string $dir Path to directory.
 */
function rocket_uninstall_rrmdir( $dir ) {

	if ( ! is_dir( $dir ) ) {
		@unlink( $dir );
		return;
	}

	$globs = glob( $dir . '/*', GLOB_NOSORT );
	if ( $globs ) {
		foreach ( $globs as $file ) {
			is_dir( $file ) ? rocket_uninstall_rrmdir( $file ) : @unlink( $file );
		}
	}

	@rmdir( $dir );

}

rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'wp-rocket/' );
rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'min/' );
rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'busting/' );
rocket_uninstall_rrmdir( WP_CONTENT_DIR . '/wp-rocket-config/' );
