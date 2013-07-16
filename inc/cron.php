<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Customizing the time interval between automatic cache purge
 * This setting can be changed from the options page of the plugin
 * By default, the interval is 4 hours
 *
 * since 1.0
 *
 */

add_filter( 'cron_schedules', 'rocket_purge_cron_schedule' );
function rocket_purge_cron_schedule( $schedules )
{

	$schedules['rocket_purge'] = array(
		'interval'	=> get_rocket_cron_interval(),
		'display' 	=> 'WP Rocket Purge',
	);

	return $schedules;
}



/**
 * Planning cron
 * If the task is not programmed, it is automatically triggered
 *
 * since 1.0
 *
 */

add_action( 'plugins_loaded', 'rocket_purge_cron_scheduled' );
function rocket_purge_cron_scheduled()
{

	if( !wp_next_scheduled( 'rocket_purge_time_event' ) )
		wp_schedule_event( time() + get_rocket_cron_interval(), 'rocket_purge', 'rocket_purge_time_event' );

}



/**
 * This event is launched when the cron is triggered
 * That delete the domain cache
 *
 * since 1.0
 *
 */

add_action( 'rocket_purge_time_event', 'do_rocket_purge_cron' );
function do_rocket_purge_cron() {
				
	// Purge all cache files when user save options
	rocket_clean_domain();
	
	// Run WP Rocket Bot for preload cache files
    run_rocket_bot( 'cache-preload' );
	
}