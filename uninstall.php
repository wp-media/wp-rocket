<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

// Delete all transients.
$rocket_transients = [
	'wp_rocket_customer_data',
	'rocket_notice_missing_tags',
	'rocket_clear_cache',
	'rocket_check_key_errors',
	'rocket_send_analytics_data',
	'rocket_critical_css_generation_process_running',
	'rocket_critical_css_generation_process_complete',
	'rocket_critical_css_generation_triggered',
	'rocketcdn_status',
	'rocketcdn_pricing',
	'rocketcdn_purge_cache_response',
	'rocket_cloudflare_ips',
	'rocket_cloudflare_is_api_keys_valid',
	'rocket_preload_triggered',
	'rocket_preload_complete',
	'rocket_preload_complete_time',
	'rocket_preload_errors',
	'rocket_database_optimization_process',
	'rocket_database_optimization_process_complete',
];

foreach ( $rocket_transients as $rocket_transient ) {
	delete_transient( $rocket_transient );
}

delete_site_transient( 'wp_rocket_update_data' );

// Delete WP Rocket options.
$rocket_options = [
	'wp_rocket_settings',
	'rocket_analytics_notice_displayed',
	'rocketcdn_user_token',
	'rocketcdn_process',
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
	'rocketcdn_check_subscription_status_event',
	'rocket_cron_deactivate_cloudflare_devmode',
];

foreach ( $rocket_events as $rocket_event ) {
	wp_clear_scheduled_hook( $rocket_event );
}

/**
 * Remove all WP Rocket files.
 *
 * @since 1.2.0
 *
 * @param string $dir Path to file or directory to remove.
 */
function rocket_uninstall_rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		@unlink( $dir );
		return;
	}

	$items = @scandir( $dir );

	if ( ! $items ) {
		return;
	}

	// Get rid of dot files when present.
	if ( '.' === $items[0] ) {
		unset( $items[0], $items[1] );

		// Reindex back to 0.
		$items = array_values( $items );
	}

	$dir = trailingslashit( $dir );

	$items = array_map(
		function ( $item ) use ( $dir ) {
			return "{$dir}{$item}";
		},
		$items
	);

	foreach ( $items as $item ) {
		if ( is_dir( $item ) ) {
			rocket_uninstall_rrmdir( $item );
		} else {
			@unlink( $item );
		}
	}

	@rmdir( $dir );
}

rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'wp-rocket/' );
rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'min/' );
rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'busting/' );
rocket_uninstall_rrmdir( WP_ROCKET_CACHE_ROOT_PATH . 'critical-css/' );
rocket_uninstall_rrmdir( WP_ROCKET_CONFIG_PATH );
