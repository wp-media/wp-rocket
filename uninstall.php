<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

// Delete all transients.
delete_site_transient( 'wp_rocket_update_data' );

$rocket_transients = [
	'rocket_cloudflare_ips',
	'rocket_send_analytics_data',
];

foreach ( $rocket_transients as $rocket_transient ) {
	delete_transient( $rocket_transient );
}

// Delete WP Rocket options.
$rocket_options = [
	'wp_rocket_settings',
	'rocket_analytics_notice_displayed',
];

foreach ( $rocket_options as $rocket_option ) {
	delete_option( $rocket_option );
}

// Delete all user meta related to WP Rocket.
delete_metadata( 'user', '', 'rocket_boxes', '', true );

// Clear scheduled WP Rocket Cron.
$rocket_events = [
	'rocket_purge_time_event',
	'rocket_database_optimization_time_event',
	'rocket_google_tracking_cache_update',
	'rocket_facebook_tracking_cache_update',
	'rocket_cache_dir_size_check',
];

foreach ( $rocket_events as $rocket_event ) {
	wp_clear_scheduled_hook( $rocket_event );
}

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
