<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Adds new intervals for cron jobs
 *
 * Customizes the time interval between automatic cache purge
 * This setting can be changed from the options page of the plugin
 * By default, the interval is 24 hours
 *
 * Adds a weekly/monthly interval for database optimization
 *
 * @since 2.8.9 Add weekly and monthly intervals
 * @since 1.0
 *
 * @param Array $schedules An array of intervals used by cron jobs
 * @return Array Updated array of intervals
 */
add_filter( 'cron_schedules', 'rocket_purge_cron_schedule' );
function rocket_purge_cron_schedule( $schedules ) {
	if ( 0 < (int) get_rocket_option( 'purge_cron_interval' ) ) {
		$schedules['rocket_purge'] = array(
			'interval'	=> get_rocket_purge_cron_interval(),
			'display' 	=> sprintf( __( '%s clear', 'rocket' ), WP_ROCKET_PLUGIN_NAME )
		);
	}

    if ( get_rocket_option( 'schedule_automatic_cleanup', false ) ) {
        switch ( get_rocket_option( 'automatic_cleanup_frequency' ) ) {
            case 'weekly':
                $schedules['weekly'] = array(
                    'interval' => 604800,
                    'display'  => __( 'weekly', 'rocket' )
                );
                break;
            case 'monthly':
                $schedules['monthly'] = array(
                    'interval' => 2592000,
                    'display'  => __( 'monthly', 'rocket' ) 
                );
                break;
        }
    }

	return $schedules;
}

/**
 * Planning cron
 * If the task is not programmed, it is automatically triggered
 *
 * @since 1.0
 */
add_action( 'init', 'rocket_purge_cron_scheduled' );
function rocket_purge_cron_scheduled() {
	if ( 0 < (int) get_rocket_option( 'purge_cron_interval' ) && ! wp_next_scheduled( 'rocket_purge_time_event' ) ) {
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
function do_rocket_purge_cron() {
	// Purge domain cache files
	rocket_clean_domain();

	// Run WP Rocket Bot for preload cache files
	run_rocket_preload_cache( 'cache-preload' );
}

/**
 * Planning database optimization cron
 * If the task is not programmed, it is automatically triggered
 *
 * @since 2.8
 * @author Remy Perona
 */
add_action( 'init', 'rocket_database_optimization_scheduled' );
function rocket_database_optimization_scheduled() {
    if ( get_rocket_option( 'schedule_automatic_cleanup', false ) ) {
        if ( ! wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
            wp_schedule_event( time(), get_rocket_option( 'automatic_cleanup_frequency', 'weekly' ), 'rocket_database_optimization_time_event' );
        }
    }
}

/**
 * This event is launched when the cron is triggered
 * Performs the database optimization
 *
 * @since 2.8
 * @author Remy Perona
 */
add_action( 'rocket_database_optimization_time_event', 'do_rocket_database_optimization' );