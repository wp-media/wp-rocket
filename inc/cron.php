<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Customizing the time interval between automatic cache purge
 * This setting can be changed from the options page of the plugin
 * By default, the interval is 4 hours
 *
 * @since 1.0
 */
add_filter( 'cron_schedules', 'rocket_purge_cron_schedule' );
function rocket_purge_cron_schedule( $schedules )
{
	$schedules['rocket_purge'] = array(
		'interval'	=> get_rocket_purge_cron_interval(),
		'display' 	=> sprintf( __( '%s clear', 'rocket' ), WP_ROCKET_PLUGIN_NAME )
	);
	return $schedules;
}

/**
 * Planning cron
 * If the task is not programmed, it is automatically triggered
 *
 * @since 1.0
 */
add_action( 'init', 'rocket_purge_cron_scheduled' );
function rocket_purge_cron_scheduled()
{
	if ( ! wp_next_scheduled( 'rocket_purge_time_event' ) ) {
		wp_schedule_event( time() + get_rocket_purge_cron_interval(), 'rocket_purge', 'rocket_purge_time_event' );
	}
}

/**
 * This event is launched when the cron is triggered
 * Purge all cache files when user save options
 *
 * @since 2.0 Clear cache files for all langs when a plugin translation is activated
 * @since 1.0
 */
add_action( 'rocket_purge_time_event', 'do_rocket_purge_cron' );
function do_rocket_purge_cron()
{
	// Purge domain cache files
	rocket_clean_domain();

	// Purge minify cache files
	rocket_clean_minify();

	// Run WP Rocket Bot for preload cache files
	run_rocket_bot( 'cache-preload' );
}