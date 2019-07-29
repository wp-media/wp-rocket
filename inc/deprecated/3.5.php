<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! function_exists( 'get_rocket_purge_cron_interval' ) ) :
	/**
	 * Get the interval task cron purge in seconds
	 * This setting can be changed from the options page of the plugin
	 *
	 * @since 1.0
	 * @deprecated 3.5
	 *
	 * @return int The interval task cron purge in seconds
	 */
	function get_rocket_purge_cron_interval() {
		_deprecated_function( __FUNCTION__ . '()', '3.5' );

		if ( ! get_rocket_option( 'purge_cron_interval' ) || ! get_rocket_option( 'purge_cron_unit' ) ) {
			return 0;
		}
		return (int) ( get_rocket_option( 'purge_cron_interval' ) * constant( get_rocket_option( 'purge_cron_unit' ) ) );
	}
endif;

if ( ! function_exists( 'rocket_purge_cron_schedule' ) ) :
	/**
	 * Adds new intervals for cron jobs
	 *
	 * Customizes the time interval between automatic cache purge
	 * This setting can be changed from the options page of the plugin
	 * By default, the interval is 24 hours
	 *
	 * Adds a weekly/monthly interval for database optimization
	 *
	 * @since 1.0
	 * @since 2.8.9 Add weekly and monthly intervals
	 * @deprecated 3.5
	 *
	 * @param Array $schedules An array of intervals used by cron jobs.
	 * @return Array Updated array of intervals
	 */
	function rocket_purge_cron_schedule( $schedules ) {
		_deprecated_function( __FUNCTION__ . '()', '3.5' );

		if ( 0 < (int) get_rocket_option( 'purge_cron_interval' ) ) {
			$schedules['rocket_purge'] = array(
				'interval' => get_rocket_purge_cron_interval(),
				// translators: %s = WP Rocket name.
				'display'  => sprintf( __( '%s clear', 'rocket' ), WP_ROCKET_PLUGIN_NAME ),
			);
		}

		if ( get_rocket_option( 'schedule_automatic_cleanup', false ) ) {
			switch ( get_rocket_option( 'automatic_cleanup_frequency' ) ) {
				case 'weekly':
					$schedules['weekly'] = array(
						'interval' => 604800,
						'display'  => __( 'weekly', 'rocket' ),
					);
					break;
				case 'monthly':
					$schedules['monthly'] = array(
						'interval' => 2592000,
						'display'  => __( 'monthly', 'rocket' ),
					);
					break;
			}
		}

		return $schedules;
	}
endif;

if ( ! function_exists( 'rocket_purge_cron_schedule' ) ) :
	/**
	 * Planning cron
	 * If the task is not programmed, it is automatically triggered
	 *
	 * @since 1.0
	 * @deprecated 3.5
	 */
	function rocket_purge_cron_scheduled() {
		_deprecated_function( __FUNCTION__ . '()', '3.5' );

		if ( 0 < (int) get_rocket_option( 'purge_cron_interval' ) && ! wp_next_scheduled( 'rocket_purge_time_event' ) ) {
			wp_schedule_event( time() + get_rocket_purge_cron_interval(), 'rocket_purge', 'rocket_purge_time_event' );
		}
	}
endif;

if ( ! function_exists( 'do_rocket_purge_cron' ) ) :
	/**
	 * This event is launched when the cron is triggered
	 * Purge all cache files when user save options
	 *
	 * @since 1.0
	 * @since 2.0 Clear cache files for all langs when a plugin translation is activated
	 * @deprecated 3.5
	 */
	function do_rocket_purge_cron() {
		_deprecated_function( __FUNCTION__ . '()', '3.5' );

		// Purge domain cache files.
		rocket_clean_domain();
	}
endif;
