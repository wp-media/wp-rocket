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

/*
* Cron update for the plugin
*
* @since 2.4.2
*/
add_action( 'rocket_cron_auto_update', 'rocket_launch_autoupdate' );
function rocket_launch_autoupdate() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/misc.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		
	$title = __( 'Update Plugin' );
	$plugin = 'wp-rocket/wp-rocket.php';
	$nonce = 'upgrade-plugin_' . $plugin;
	$url = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin ); 			
	$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
	$upgrader = new Plugin_Upgrader( $upgrader_skin );
	$wp_upgrader = new WP_Upgrader();
	$wp_upgrader->fs_connect();
	$wp_upgrader->maintenance_mode( true );
	$upgrader->upgrade( $plugin );
	$wp_upgrader->maintenance_mode( false );
	$upgrader_skin->after();
}

/*
* Set up the cron on single event if a zip is available and the option activated
*
* @since 2.4.2
*/
add_action( 'init', 'rocket_set_autoupdate_cron' );
function rocket_set_autoupdate_cron() {
	$plugin_transient = get_site_transient( 'update_plugins' );
	$c_key = get_rocket_option( 'consumer_key' );
	if ( ! wp_next_scheduled( 'rocket_cron_auto_update' ) && get_rocket_option( 'autoupdate' ) &&
		isset( $plugin_transient->response['wp-rocket/wp-rocket.php']->package, $plugin_transient->response['wp-rocket/wp-rocket.php']->new_version ) && 
		sprintf( 'http://support.wp-rocket.me/%s/wp-rocket_%s.zip', $c_key, $plugin_transient->response['wp-rocket/wp-rocket.php']->new_version ) == $plugin_transient->response['wp-rocket/wp-rocket.php']->package
		)
	{
		wp_schedule_single_event( time()+1, 'rocket_cron_auto_update' );
	}
}