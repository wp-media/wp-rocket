<?php

/*
Plugin Name: WP Rocket
Plugin URI: http://www.wp-rocket.me
Description: The best WordPress performance plugin.
Version: 1.1.0
Author: WP-Rocket
Contributors: Jonathan Buttigieg, Julio Potier
Author URI: http://www.wp-rocket.me

Copyright 2013 WP Rocket

*/
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

define( 'WP_ROCKET_VERSION'			, '1.1.0');
define( 'WP_ROCKET_SLUG'			, 'wp_rocket_settings');
if( !defined( 'WP_ROCKET_ENV' ) )
	define( 'WP_ROCKET_ENV', '' );
define( 'WP_ROCKET_WEB_MAIN'		, 'http://'.WP_ROCKET_ENV.'wp-rocket.me/');
define( 'WP_ROCKET_WEB_CHECK'		, 'check_update.php');
define( 'WP_ROCKET_WEB_VALID'		, 'valid_key.php');
define( 'WP_ROCKET_WEB_INFO'		, 'plugin_information.php');
define( 'WP_ROCKET_WEB_SUPPORT'		, 'http://support.wp-rocket.me/forum/fr/');
define( 'WP_ROCKET_FILE'			, __FILE__ );
define( 'WP_ROCKET_PATH'			, realpath( plugin_dir_path(__FILE__) ).'/' );
define( 'WP_ROCKET_INC_PATH'		, realpath( WP_ROCKET_PATH . 'inc/' ) . '/' );
define( 'WP_ROCKET_FRONT_PATH'		, realpath( WP_ROCKET_INC_PATH . 'front/' ) . '/' );
define( 'WP_ROCKET_ADMIN_PATH'		, realpath( WP_ROCKET_INC_PATH . 'admin' ) . '/' );
define( 'WP_ROCKET_CACHE_PATH'		, realpath( WP_ROCKET_PATH . 'cache' ) . '/' );
define( 'WP_ROCKET_URL'				, plugin_dir_url(__FILE__) );
define( 'WP_ROCKET_INC_URL'			, WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL'		, WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL'	, WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_CACHE_URL'		, WP_ROCKET_URL . 'cache/' );

if( !defined( 'CHMOD_WP_ROCKET_CACHE_DIRS' ) )
	define( 'CHMOD_WP_ROCKET_CACHE_DIRS', 0755 );

if( !defined( 'SECOND_IN_SECONDS' ) )
	define( 'SECOND_IN_SECONDS', 1 );
if( !defined( 'MINUTE_IN_SECONDS' ) )
	define( 'MINUTE_IN_SECONDS', SECOND_IN_SECONDS*60 );
if( !defined( 'HOUR_IN_SECONDS' ) )
	define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
if( !defined( 'DAY_IN_SECONDS' ) )
	define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
if( !defined( 'WEEK_IN_SECONDS' ) )
	define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
if( !defined( 'YEAR_IN_SECONDS' ) )
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );

/*
 * Tell WP what to do when plugin is loaded
 *
 * since 1.0
 *
 */

add_action( 'plugins_loaded', 'rocket_init' );
function rocket_init()
{

	if( defined( 'DOING_AUTOSAVE' ) )
			return;

	// Call all classes and functions
	require WP_ROCKET_INC_PATH . '/functions.php';
	require WP_ROCKET_FRONT_PATH . '/htaccess.php';

	if( rocket_valid_key() ) {
		require WP_ROCKET_INC_PATH . '/purge.php';
		require WP_ROCKET_INC_PATH . '/admin-bar.php';
	}

	$options = get_option( WP_ROCKET_SLUG );

	if( rocket_valid_key() )
		if( isset( $options['purge_cron_interval'] ) && (int)$options['purge_cron_interval'] > 0 )
			require  WP_ROCKET_INC_PATH . '/cron.php';


	if( is_admin() )
	{

		require WP_ROCKET_ADMIN_PATH . '/upgrader.php';
		require WP_ROCKET_ADMIN_PATH . '/updater.php';
		require WP_ROCKET_ADMIN_PATH . '/options.php';
		require WP_ROCKET_ADMIN_PATH . '/notices.php';
		require WP_ROCKET_ADMIN_PATH . '/admin.php';
	}
	elseif( rocket_valid_key() )
	{
		require WP_ROCKET_FRONT_PATH . '/process.php';
		require WP_ROCKET_FRONT_PATH . '/minify.php';
		require WP_ROCKET_FRONT_PATH . '/cookie.php';
		require WP_ROCKET_FRONT_PATH . '/images.php';

		if( isset( $options['lazyload'] ) && $options['lazyload'] == '1' )
			require WP_ROCKET_FRONT_PATH . '/lazyload.php';
	}

	// You can hook this to trigger any action when WP Rocket is correctly loaded, so, not in AJAX or AUTOSAVE mode
	if( rocket_valid_key() )
		do_action( 'wp_rocket_loaded' );

}



/*
 * Tell WP what to do when plugin is deactivated
 *
 * since 1.0
 *
 */
register_deactivation_hook( __FILE__, 'rocket_deactivation' );
function rocket_deactivation()
{
	// Delete All WP Rocket rules of the .htaccess file
	flush_rocket_htaccess( true );
	flush_rewrite_rules();
}



/*
 * Tell WP what to do when plugin is activated
 *
 * since 1.1.0
 *
 */
register_activation_hook( __FILE__, 'rocket_activation' );
function rocket_activation()
{
	require WP_ROCKET_INC_PATH . '/functions.php';
	require WP_ROCKET_FRONT_PATH . '/htaccess.php';
	
	// Add All WP Rocket rules of the .htaccess file
	flush_rocket_htaccess();
	flush_rewrite_rules();
}