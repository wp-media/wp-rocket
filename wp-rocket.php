<?php

/*
Plugin Name: WP Rocket
Plugin URI: http://www.wp-rocket.me
Description: The best WordPress performance plugin.
Version: 1.0
Author: Jonathan Buttigieg, Julio Potier
Author URI: http://www.wp-rocket.me

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


// Call all class and functions
require WP_ROCKET_INC_PATH . 'functions.php';
require WP_ROCKET_INC_PATH . 'purge.php';
require WP_ROCKET_INC_PATH . 'admin-bar.php';
require WP_ROCKET_FRONT_PATH . 'htaccess.php';


add_action( 'plugins_loaded', 'wp_rocket_init' );
function wp_rocket_init()
{

	$options = get_option( 'wp_rocket_settings' );

	if( $options['purge_cron_interval'] > 0 )
		require  WP_ROCKET_INC_PATH . 'cron.php';


	if( is_admin() )
	{
		require WP_ROCKET_ADMIN_PATH . 'options.php';
		require WP_ROCKET_ADMIN_PATH . 'notices.php';
	}
	else
	{
		require WP_ROCKET_FRONT_PATH . 'process.php';
		require WP_ROCKET_FRONT_PATH . 'minify.php';
		require WP_ROCKET_FRONT_PATH . 'cookie.php';

		if( isset( $options['lazyload'] ) && $options['lazyload'] == '1' )
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
	if( !get_option( 'wp_rocket_settings' ) )
	{

		add_option( 'wp_rocket_settings',
			array(
				'purge_cron_interval'  => 14400, // 4 hours
				'cache_mobile'         => 0,
				'cache_reject_uri'     => array(),
				'cache_reject_cookies' => array(),
				'lazyload'			   => 1,
				'minify_css'		   => 0,
				'exclude_css'		   => array(),
				'minify_js'			   => 0,
				'exclude_js'		   => array()
			)
		);

	}

	//
	flush_rocket_htaccess();
	flush_rewrite_rules();
}