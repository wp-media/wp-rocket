<?php

/*
Plugin Name: WP Rocket
Plugin URI: http://www.wp-rocket.me
Description: The best WordPress performance plugin.
Version: 2.0
Author: WP Rocket
Contributors: Jonathan Buttigieg, Julio Potier
Author URI: http://www.wp-rocket.me

Copyright 2013 WP Rocket

*/
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

// Rocket defines
define( 'WP_ROCKET_VERSION'             , '2.0');
define( 'WP_ROCKET_SLUG'                , 'wp_rocket_settings');
define( 'WP_ROCKET_WEB_MAIN'            , 'http://support.wp-rocket.me/');
define( 'WP_ROCKET_WEB_CHECK'           , WP_ROCKET_WEB_MAIN.'check_update.php');
define( 'WP_ROCKET_WEB_VALID'           , WP_ROCKET_WEB_MAIN.'valid_key.php');
define( 'WP_ROCKET_WEB_INFO'            , WP_ROCKET_WEB_MAIN.'plugin_information.php');
define( 'WP_ROCKET_WEB_SUPPORT'         , WP_ROCKET_WEB_MAIN.'forum/fr/');
define( 'WP_ROCKET_BOT_URL'             , 'http://bot.wp-rocket.me/launch.php');
define( 'WP_ROCKET_FILE'                , __FILE__ );
define( 'WP_ROCKET_PATH'                , realpath( plugin_dir_path( WP_ROCKET_FILE ) ).'/' );
define( 'WP_ROCKET_INC_PATH'            , realpath( WP_ROCKET_PATH . 'inc/' ) . '/' );
define( 'WP_ROCKET_CONFIG_PATH'         , realpath( WP_ROCKET_PATH . 'config/' ) . '/' );
define( 'WP_ROCKET_FRONT_PATH'          , realpath( WP_ROCKET_INC_PATH . 'front/' ) . '/' );
define( 'WP_ROCKET_ADMIN_PATH'          , realpath( WP_ROCKET_INC_PATH . 'admin' ) . '/' );
define( 'WP_ROCKET_CACHE_PATH'          , WP_CONTENT_DIR . '/cache/wp-rocket/' );
define( 'WP_ROCKET_URL'                 , plugin_dir_url( WP_ROCKET_FILE ) );
define( 'WP_ROCKET_INC_URL'             , WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL'           , WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL'        , WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_URL'           , WP_ROCKET_INC_URL . 'admin/' );
define( 'WP_ROCKET_ADMIN_JS_URL'        , WP_ROCKET_ADMIN_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_CSS_URL'       , WP_ROCKET_ADMIN_URL . 'css/' );
define( 'WP_ROCKET_ADMIN_IMG_URL'       , WP_ROCKET_ADMIN_URL . 'img/' );
define( 'WP_ROCKET_CACHE_URL'           , WP_CONTENT_URL . '/cache/wp-rocket/' );
if( !defined( 'CHMOD_WP_ROCKET_CACHE_DIRS' ) )
	define( 'CHMOD_WP_ROCKET_CACHE_DIRS', 0755 );

// WP <3.5 defines
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
 * @since 1.0
 *
 */
 
add_action( 'plugins_loaded', 'rocket_init' );
function rocket_init()
{
    
    // Load translations
	load_plugin_textdomain( 'rocket', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    
    // Nothing to do if autosave
    if( defined( 'DOING_AUTOSAVE' ) )
        return;
		
    // Necessary to call correctly WP Rocket Bot for cache json
    global $do_rocket_bot_cache_json;
    $do_rocket_bot_cache_json = false;

    // Call defines,  classes and functions
    require WP_ROCKET_INC_PATH . '/functions.php';
    require WP_ROCKET_PATH . '/deprecated.php';
    require WP_ROCKET_FRONT_PATH . '/htaccess.php';
    require WP_ROCKET_INC_PATH . '/headers.php';

    if( rocket_valid_key() )
    {
        require WP_ROCKET_INC_PATH . '/purge.php';
        require WP_ROCKET_INC_PATH . '/admin-bar.php';

        if( (int)get_rocket_option( 'purge_cron_interval' ) > 0 )
            require  WP_ROCKET_INC_PATH . '/cron.php';
    }

    if( is_admin() )
    {
        require WP_ROCKET_ADMIN_PATH . '/upgrader.php';
        require WP_ROCKET_ADMIN_PATH . '/updater.php';
        require WP_ROCKET_ADMIN_PATH . '/options.php';
        require WP_ROCKET_ADMIN_PATH . '/notices.php';
        require WP_ROCKET_ADMIN_PATH . '/admin.php';
        require WP_ROCKET_ADMIN_PATH . '/pointers.php';
    }
    elseif( rocket_valid_key() )
    {
        //require WP_ROCKET_FRONT_PATH . '/process.php';
        require WP_ROCKET_FRONT_PATH . '/minify.php';
        require WP_ROCKET_FRONT_PATH . '/cookie.php';
        require WP_ROCKET_FRONT_PATH . '/images.php';
        require WP_ROCKET_FRONT_PATH . '/enqueue.php';
        require WP_ROCKET_FRONT_PATH . '/dns-prefetch.php';

        if( get_rocket_option( 'deferred_js_files' ) )
            require WP_ROCKET_FRONT_PATH . '/deferred-js.php';

        if( get_rocket_option( 'lazyload' ) == '1' )
			require WP_ROCKET_FRONT_PATH . '/lazyload.php';
    }

    // You can hook this to trigger any action when WP Rocket is correctly loaded, so, not in AUTOSAVE mode
    if( rocket_valid_key() )
		do_action( 'wp_rocket_loaded' );
}



/*
 * Tell WP what to do when plugin is deactivated
 *
 * @since 1.0
 *
 */

register_deactivation_hook( __FILE__, 'rocket_deactivation' );
function rocket_deactivation()
{

    require WP_ROCKET_INC_PATH . '/wp-config.php';

    // Delete All WP Rocket rules of the .htaccess file
    flush_rocket_htaccess( true );
    flush_rewrite_rules();

    // Remove WP_CACHE constant in wp-config.php
	set_rocket_wp_cache_define( false );

    // Delete content of advanced-cache.php file
    rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
}



/*
 * Tell WP what to do when plugin is activated
 *
 * @since 1.1.0
 *
 */

register_activation_hook( __FILE__, 'rocket_activation' );
function rocket_activation()
{
    require WP_ROCKET_INC_PATH . '/functions.php';
    require WP_ROCKET_INC_PATH . '/wp-config.php';
    require WP_ROCKET_FRONT_PATH . '/htaccess.php';

    // Add All WP Rocket rules of the .htaccess file
    flush_rocket_htaccess();
    flush_rewrite_rules();

    // Create cache folder if not exist
    if( !is_dir( WP_ROCKET_CACHE_PATH ) )
	    rocket_mkdir_p( WP_ROCKET_CACHE_PATH );

	// Add WP_CACHE constant in wp-config.php
	set_rocket_wp_cache_define();

	// Create advanced-cache.php file
	rocket_generate_advanced_cache_file();

	// Create config file
	rocket_generate_config_file();
}