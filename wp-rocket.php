<?php

/*
Plugin Name: WP Rocket
Plugin URI: http://www.wp-rocket.me
Description: The best WordPress performance plugin
Version: 1.0
Author: Jonathan Buttigieg, Julio Potier
Author URI: http://www.wp-rocket.me.

Copyright 2013 WP Rocket

*/


define( 'WP_ROCKET_VERSION'			, '1.0');

define( 'WP_ROCKET_PATH'			, plugin_dir_path(__FILE__) );
define( 'WP_ROCKET_INC_PATH'		, WP_ROCKET_PATH . 'inc/' );
define( 'WP_ROCKET_FRONT_PATH'		, WP_ROCKET_INC_PATH . 'front/' );
define( 'WP_ROCKET_ADMIN_PATH'		, WP_ROCKET_INC_PATH . 'admin/' );
define( 'WP_ROCKET_CACHE_PATH'		, WP_ROCKET_PATH . 'cache/' );

define( 'WP_ROCKET_URL'				, plugin_dir_url(__FILE__) );
define( 'WP_ROCKET_INC_URL'			, WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL'		, WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL'	, WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_CACHE_URL'		, WP_ROCKET_URL . 'cache/' );

// TO DO - description
if( !defined( 'WP_ROCKET_EXPIRATION_TIME' ) )
	define( 'WP_ROCKET_EXPIRATION_TIME', 60 );


// Call all class and functions
require  WP_ROCKET_INC_PATH . 'functions.php';
require  WP_ROCKET_INC_PATH . 'purge.php';
require  WP_ROCKET_INC_PATH . 'cron.php';
require  WP_ROCKET_ADMIN_PATH . 'admin-bar.php';
require  WP_ROCKET_FRONT_PATH . 'cookie.php';
require  WP_ROCKET_FRONT_PATH . 'htaccess.php';

if( is_admin() )
{

	//
	add_filter( 'mod_rewrite_rules', 'rocket_override_mod_rewrite_rules' );
}


add_action( 'plugins_loaded', 'wp_rocket_init' );
function wp_rocket_init()
{

	if( is_admin() ) {
		require  WP_ROCKET_ADMIN_PATH . 'errors.php';
	}
	else 
	{
		require WP_ROCKET_FRONT_PATH . 'lazyload.php';
	}

}



/*
 * Tell WP what to do when plugin is deactivated
 *
 * since 1.0
 *
 */
register_deactivation_hook(__FILE__, 'wp_rocket_deactivation' );
function wp_rocket_deactivation()
{

	// Delete All WP Rocket rules of the .htaccess file
	remove_filter( 'mod_rewrite_rules', 'rocket_override_mod_rewrite_rules' );
	flush_rocket_htaccess( true );
	flush_rewrite_rules();

}



/*
 * Tell WP what to do when plugin is activated
 *
 * since 1.0
 *
 */
register_activation_hook(__FILE__, 'wp_rocket_activation' );
function wp_rocket_activation()
{


	// Create option
	if( !get_option( 'wp_rocket_settings' ) ) {

		add_option( 'wp_rocket_settings',
			array(
				'purge_cron_interval'  => 14400, // 4 hours
				'cache_not_logged_in'  => 1,
				'cache_mobile'         => 0,
				'lazyload'			   => 1
			)
		);

	}

	//
	flush_rocket_launcher();

	//
	flush_rocket_htaccess();

	//
	flush_rewrite_rules();

}