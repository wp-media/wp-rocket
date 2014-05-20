<?php
/*
Plugin Name: WP Rocket
Plugin URI: http://www.wp-rocket.me
Description: The best WordPress performance plugin.
Version: 2.2
Author: WP Rocket
Contributors: Jonathan Buttigieg, Julio Potier
Author URI: http://www.wp-rocket.me

Text Domain: rocket
Domain Path: languages

Copyright 2013-2014 WP Rocket
*/

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Rocket defines
define( 'WP_ROCKET_VERSION'             , '2.2');
define( 'WP_ROCKET_PRIVATE_KEY'         , false );
define( 'WP_ROCKET_SLUG'                , 'wp_rocket_settings');
define( 'WP_ROCKET_WEB_MAIN'            , 'http://support.wp-rocket.me/');
define( 'WP_ROCKET_WEB_CHECK'           , WP_ROCKET_WEB_MAIN.'check_update2.php');
define( 'WP_ROCKET_WEB_VALID'           , WP_ROCKET_WEB_MAIN.'valid_key2.php');
define( 'WP_ROCKET_WEB_INFO'            , WP_ROCKET_WEB_MAIN.'plugin_information2.php');
define( 'WP_ROCKET_WEB_SUPPORT'         , WP_ROCKET_WEB_MAIN.'forums/');
define( 'WP_ROCKET_BOT_URL'             , 'http://bot.wp-rocket.me/launch.php');
define( 'WP_ROCKET_ACCOUNT_URL'         , 'http://support.wp-rocket.me/my-account/');
define( 'WP_ROCKET_FILE'                , __FILE__ );
define( 'WP_ROCKET_PATH'                , realpath( plugin_dir_path( WP_ROCKET_FILE ) ).'/' );
define( 'WP_ROCKET_INC_PATH'            , realpath( WP_ROCKET_PATH . 'inc/' ) . '/' );
define( 'WP_ROCKET_FRONT_PATH'          , realpath( WP_ROCKET_INC_PATH . 'front/' ) . '/' );
define( 'WP_ROCKET_ADMIN_PATH'          , realpath( WP_ROCKET_INC_PATH . 'admin' ) . '/' );
define( 'WP_ROCKET_FUNCTIONS_PATH'      , realpath( WP_ROCKET_INC_PATH . 'functions' ) . '/' );
define( 'WP_ROCKET_CONFIG_PATH'         , WP_CONTENT_DIR . '/wp-rocket-config/' );
define( 'WP_ROCKET_CACHE_PATH'          , WP_CONTENT_DIR . '/cache/wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_PATH'   , WP_CONTENT_DIR . '/cache/min/' );
define( 'WP_ROCKET_URL'                 , plugin_dir_url( WP_ROCKET_FILE ) );
define( 'WP_ROCKET_INC_URL'             , WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL'           , WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL'        , WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_LAB_JS_VERSION'      , '2.0.3' );
define( 'WP_ROCKET_ADMIN_URL'           , WP_ROCKET_INC_URL . 'admin/' );
define( 'WP_ROCKET_ADMIN_JS_URL'        , WP_ROCKET_ADMIN_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_CSS_URL'       , WP_ROCKET_ADMIN_URL . 'css/' );
define( 'WP_ROCKET_CACHE_URL'           , WP_CONTENT_URL . '/cache/wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_URL'    , WP_CONTENT_URL . '/cache/min/' );
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
    if ( defined( 'DOING_AUTOSAVE' ) ) {
        return;
    }

    // Necessary to call correctly WP Rocket Bot for cache json
    global $do_rocket_bot_cache_json;
    $do_rocket_bot_cache_json = false;

    // Call defines,  classes and functions
    require WP_ROCKET_FUNCTIONS_PATH . '/options.php';

    // Last constants
    define( 'WP_ROCKET_PLUGIN_NAME', get_rocket_option( 'wl_plugin_name', 'WP Rocket' ) );
    define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

    // Call defines,  classes and functions
	require( WP_ROCKET_FUNCTIONS_PATH	. '/files.php' );
    require( WP_ROCKET_FUNCTIONS_PATH	. '/posts.php' );
    require( WP_ROCKET_FUNCTIONS_PATH	. '/admin.php' );
    require( WP_ROCKET_FUNCTIONS_PATH	. '/formatting.php' );
    require( WP_ROCKET_FUNCTIONS_PATH	. '/plugins.php' );
    require( WP_ROCKET_FUNCTIONS_PATH	. '/bots.php' );
    require( WP_ROCKET_INC_PATH			. '/deprecated.php' );
    require( WP_ROCKET_FRONT_PATH		. '/htaccess.php' );
    require( WP_ROCKET_FRONT_PATH		. '/override-plugins.php' );
    require( WP_ROCKET_INC_PATH			. '/admin-bar.php' );
	require( dirname( __FILE__ )		. '/licence-data.php' );

    if( rocket_valid_key() ) {
        require( WP_ROCKET_INC_PATH . '/purge.php' );

        if ( 0 < (int) get_rocket_option( 'purge_cron_interval' ) ) {
            require( WP_ROCKET_INC_PATH . '/cron.php' );
        }

        if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
        	require  WP_ROCKET_FRONT_PATH . '/cdn.php';
        }
        if ( defined( 'SUNRISE' ) && SUNRISE == 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
	        require( WP_ROCKET_INC_PATH . '/domain-mapping.php' );
        }
    }

    if( is_admin() ) {
        require( WP_ROCKET_ADMIN_PATH . '/upgrader.php' );
        require( WP_ROCKET_ADMIN_PATH . '/updater.php' );
        require( WP_ROCKET_ADMIN_PATH . '/class-repeater-field.php' );
        require( WP_ROCKET_ADMIN_PATH . '/options.php' );
        require( WP_ROCKET_ADMIN_PATH . '/notices.php' );
        require( WP_ROCKET_ADMIN_PATH . '/admin.php' );
    }
    elseif( rocket_valid_key() )
    {

        require( WP_ROCKET_FRONT_PATH . '/minify.php' );
        require( WP_ROCKET_FRONT_PATH . '/cookie.php' );
        require( WP_ROCKET_FRONT_PATH . '/images.php' );
        require( WP_ROCKET_FRONT_PATH . '/enqueue.php' );
        require( WP_ROCKET_FRONT_PATH . '/dns-prefetch.php' );

        if( get_rocket_option( 'deferred_js_files' ) ) {
	       require( WP_ROCKET_FRONT_PATH . '/deferred-js.php' );
        }

        if( '1' == get_rocket_option( 'lazyload' ) ) {
	       require( WP_ROCKET_FRONT_PATH . '/lazyload.php' );
        }

    }

    // You can hook this to trigger any action when WP Rocket is correctly loaded, so, not in AUTOSAVE mode
	if ( rocket_valid_key() ) {
		/**
		 * Fires when WP Rocket is correctly loaded
		 *
		 * @since 1.0
		*/
		do_action( 'wp_rocket_loaded' );
    }

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

    // Check if all the job can be done
    $htaccess_file =  get_home_path() . '.htaccess';
    $config_file =  rocket_find_wpconfig_path();

    if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( $_GET['rocket_nonce'], 'force_deactivation' ) ) {

        $causes = array();

        // .htaccess problem
        global $is_apache;
        if( $is_apache && !is_writable( $htaccess_file ) ) {
            $causes[] = 'htaccess';
        }

        // wp-config problem
        if( !is_writable( $config_file ) ) {
            $causes[] = 'wpconfig';
        }

		if( count( $causes ) ) {
			global $current_user;
	        set_transient( $current_user->ID . '_donotdeactivaterocket', $causes );
	        wp_safe_redirect( wp_get_referer() );
	        die();
		}

    }

	// Delete config files
	list( $config_files_path ) = get_rocket_config_file();
	foreach( $config_files_path as $config_file ) {
		@unlink( $config_file );
	}

	if( !count( glob( WP_ROCKET_CONFIG_PATH . '*.php' ) ) ) {
		// Delete All WP Rocket rules of the .htaccess file
	    flush_rocket_htaccess( true );
	    flush_rewrite_rules();

	    // Remove WP_CACHE constant in wp-config.php
	    set_rocket_wp_cache_define( false );

	    // Delete content of advanced-cache.php
	    rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
	}

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

	// Last constants
    define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
    define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

    require( WP_ROCKET_FUNCTIONS_PATH . '/options.php' );
    require( WP_ROCKET_FUNCTIONS_PATH . '/files.php' );
    require( WP_ROCKET_FUNCTIONS_PATH . '/formatting.php' );
    require( WP_ROCKET_FUNCTIONS_PATH . '/plugins.php' );
    require( WP_ROCKET_FRONT_PATH . '/htaccess.php' );

    // Add All WP Rocket rules of the .htaccess file
	if ( rocket_valid_key() ) {
	    flush_rocket_htaccess();
	    flush_rewrite_rules();
	}

	// Create cache folder if not exist
    if ( ! is_dir( WP_ROCKET_CACHE_PATH ) ) {
	   rocket_mkdir_p( WP_ROCKET_CACHE_PATH );
    }

	// Create minify cache folder if not exist
    if ( ! is_dir( WP_ROCKET_MINIFY_CACHE_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_MINIFY_CACHE_PATH );
    }

	// Create config domain folder if not exist
    if ( ! is_dir( WP_ROCKET_CONFIG_PATH ) ) {
		rocket_mkdir_p( WP_ROCKET_CONFIG_PATH );
    }

	// Add WP_CACHE constant in wp-config.php
	set_rocket_wp_cache_define( true );

	// Create advanced-cache.php file
	rocket_generate_advanced_cache_file();

	// Create config file
	rocket_generate_config_file();

}