<?php
/**
 * Plugin Name: WP Rocket
 * Plugin URI: https://wp-rocket.me
 * Description: The best WordPress performance plugin.
 * Version: 2.10
 * Code Name: Iridonia
 * Author: WP Media
 * Contributors: Jonathan Buttigieg, Julio Potier, Remy Perona
 * Author URI: http://wp-media.me
 * Licence: GPLv2
 *
 * Text Domain: rocket
 * Domain Path: languages
 *
 * Copyright 2013-2016 WP Rocket
 * */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Rocket defines.
define( 'WP_ROCKET_VERSION'             , '2.10' );
define( 'WP_ROCKET_PRIVATE_KEY'         , false );
define( 'WP_ROCKET_SLUG'                , 'wp_rocket_settings' );
define( 'WP_ROCKET_WEB_MAIN'            , false );
define( 'WP_ROCKET_WEB_API'             , WP_ROCKET_WEB_MAIN . 'api/wp-rocket/' );
define( 'WP_ROCKET_WEB_CHECK'           , WP_ROCKET_WEB_MAIN . 'check_update.php' );
define( 'WP_ROCKET_WEB_VALID'           , WP_ROCKET_WEB_MAIN . 'valid_key.php' );
define( 'WP_ROCKET_WEB_INFO'            , WP_ROCKET_WEB_MAIN . 'plugin_information.php' );
define( 'WP_ROCKET_WEB_SUPPORT'         , WP_ROCKET_WEB_MAIN . 'forums/' );
define( 'WP_ROCKET_BOT_URL'             , 'http://bot.wp-rocket.me/launch.php' );
define( 'WP_ROCKET_FILE'                , __FILE__ );
define( 'WP_ROCKET_PATH'                , realpath( plugin_dir_path( WP_ROCKET_FILE ) ) . '/' );
define( 'WP_ROCKET_INC_PATH'            , realpath( WP_ROCKET_PATH . 'inc/' ) . '/' );
define( 'WP_ROCKET_FRONT_PATH'          , realpath( WP_ROCKET_INC_PATH . 'front/' ) . '/' );
define( 'WP_ROCKET_ADMIN_PATH'          , realpath( WP_ROCKET_INC_PATH . 'admin' ) . '/' );
define( 'WP_ROCKET_ADMIN_UI_PATH'       , realpath( WP_ROCKET_ADMIN_PATH . 'ui' ) . '/' );
define( 'WP_ROCKET_ADMIN_UI_MODULES_PATH', realpath( WP_ROCKET_ADMIN_UI_PATH . 'modules' ) . '/' );
define( 'WP_ROCKET_COMMON_PATH'         , realpath( WP_ROCKET_INC_PATH . 'common' ) . '/' );
define( 'WP_ROCKET_CLASSES_PATH'		, realpath( WP_ROCKET_INC_PATH . 'classes' ) . '/' );
define( 'WP_ROCKET_FUNCTIONS_PATH'      , realpath( WP_ROCKET_INC_PATH . 'functions' ) . '/' );
define( 'WP_ROCKET_VENDORS_PATH'      	, realpath( WP_ROCKET_INC_PATH . 'vendors' ) . '/' );
define( 'WP_ROCKET_3RD_PARTY_PATH'   	, realpath( WP_ROCKET_INC_PATH . '3rd-party' ) . '/' );
define( 'WP_ROCKET_CONFIG_PATH'         , WP_CONTENT_DIR . '/wp-rocket-config/' );
define( 'WP_ROCKET_CACHE_PATH'          , WP_CONTENT_DIR . '/cache/wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_PATH'   , WP_CONTENT_DIR . '/cache/min/' );
define( 'WP_ROCKET_CACHE_BUSTING_PATH'  , WP_CONTENT_DIR . '/cache/busting/' );
define( 'WP_ROCKET_URL'                 , plugin_dir_url( WP_ROCKET_FILE ) );
define( 'WP_ROCKET_INC_URL'             , WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL'           , WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL'        , WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_LAB_JS_VERSION'      , '2.0.3' );
define( 'WP_ROCKET_LAZYLOAD_JS_VERSION' , '1.0.5' );
define( 'WP_ROCKET_ADMIN_URL'           , WP_ROCKET_INC_URL . 'admin/' );
define( 'WP_ROCKET_ADMIN_UI_URL'        , WP_ROCKET_ADMIN_URL . 'ui/' );
define( 'WP_ROCKET_ADMIN_UI_JS_URL'     , WP_ROCKET_ADMIN_UI_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_UI_CSS_URL'    , WP_ROCKET_ADMIN_UI_URL . 'css/' );
define( 'WP_ROCKET_ADMIN_UI_IMG_URL'    , WP_ROCKET_ADMIN_UI_URL . 'img/' );
define( 'WP_ROCKET_CACHE_URL'           , WP_CONTENT_URL . '/cache/wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_URL'    , WP_CONTENT_URL . '/cache/min/' );
define( 'WP_ROCKET_CACHE_BUSTING_URL'   , WP_CONTENT_URL . '/cache/busting/' );
if ( ! defined( 'CHMOD_WP_ROCKET_CACHE_DIRS' ) ) {
	define( 'CHMOD_WP_ROCKET_CACHE_DIRS', 0755 );
}
if ( ! defined( 'WP_ROCKET_LASTVERSION' ) ) {
	define( 'WP_ROCKET_LASTVERSION', '2.8.23' );
}

require( WP_ROCKET_INC_PATH . 'compat.php' );

/**
 * Tell WP what to do when plugin is loaded.
 *
 * @since 1.0
 */
function rocket_init() {
	// Load translations from the languages directory.
	$locale = get_locale();

	// This filter is documented in /wp-includes/l10n.php.
	$locale = apply_filters( 'plugin_locale', $locale, 'rocket' );
	load_textdomain( 'rocket', WP_LANG_DIR . '/plugins/wp-rocket-' . $locale . '.mo' );

	load_plugin_textdomain( 'rocket', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Nothing to do if autosave.
	if ( defined( 'DOING_AUTOSAVE' ) ) {
		return;
	}

	// Nothing to do if XMLRPC request.
	if ( defined( 'XMLRPC_REQUEST' ) ) {
		return;
	}

	// Necessary to call correctly WP Rocket Bot for cache json.
	global $do_rocket_bot_cache_json;
	$do_rocket_bot_cache_json = false;

	// Call defines, classes and functions.
	require( WP_ROCKET_FUNCTIONS_PATH . 'options.php' );

	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', get_rocket_option( 'wl_plugin_name', 'WP Rocket' ) );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	// Call defines,  classes and functions.
	require( WP_ROCKET_CLASSES_PATH . 'background-processing.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'files.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'posts.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'admin.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'formatting.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'cdn.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'minify.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'plugins.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'i18n.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'bots.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'varnish.php' );
	require( WP_ROCKET_INC_PATH . 'deprecated.php' );
	require( WP_ROCKET_FRONT_PATH . 'plugin-compatibility.php' );
	require( WP_ROCKET_FRONT_PATH . 'theme-compatibility.php' );
	require( WP_ROCKET_3RD_PARTY_PATH . '3rd-party.php' );
	require( WP_ROCKET_COMMON_PATH . 'admin-bar.php' );
	require( WP_ROCKET_COMMON_PATH . 'updater.php' );
	require( WP_ROCKET_COMMON_PATH . 'emoji.php' );
	require( WP_ROCKET_COMMON_PATH . 'embeds.php' );
	require( dirname( __FILE__ ) . '/licence-data.php' );

	if ( rocket_valid_key() ) {
		require( WP_ROCKET_COMMON_PATH . 'purge.php' );
		require( WP_ROCKET_COMMON_PATH . 'cron.php' );

		if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
			require( WP_ROCKET_FRONT_PATH . 'cdn.php' );
		}

		if ( 0 < (int) get_rocket_option( 'do_cloudflare' ) && phpversion() >= '5.4' ) {
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Exception/AuthenticationException.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Exception/UnauthorizedException.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Api.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/IPs.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone/Cache.php' );
			require( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone/Settings.php' );
			require( WP_ROCKET_FUNCTIONS_PATH . 'cloudflare.php' );
			require( WP_ROCKET_VENDORS_PATH . 'ip_in_range.php' );
			require( WP_ROCKET_COMMON_PATH . 'cloudflare.php' );
		}

		if ( is_multisite() && defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
	        require( WP_ROCKET_INC_PATH . '/domain-mapping.php' );
		}
	}

	if ( is_admin() ) {
		require( WP_ROCKET_ADMIN_PATH . 'ajax.php' );
		require( WP_ROCKET_ADMIN_PATH . 'upgrader.php' );
		require( WP_ROCKET_ADMIN_PATH . 'updater.php' );
		require( WP_ROCKET_ADMIN_PATH . 'class-repeater-field.php' );
		require( WP_ROCKET_ADMIN_PATH . 'options.php' );
		require( WP_ROCKET_ADMIN_PATH . 'admin.php' );
		require( WP_ROCKET_ADMIN_PATH . 'plugin-compatibility.php' );
		require( WP_ROCKET_ADMIN_UI_PATH . 'enqueue.php' );
		require( WP_ROCKET_ADMIN_UI_PATH . 'notices.php' );
		require( WP_ROCKET_ADMIN_UI_PATH . 'meta-boxes.php' );
	} elseif ( rocket_valid_key() ) {
		require( WP_ROCKET_FRONT_PATH . 'minify.php' );
		require( WP_ROCKET_FRONT_PATH . 'cookie.php' );
		require( WP_ROCKET_FRONT_PATH . 'images.php' );
		require( WP_ROCKET_FRONT_PATH . 'enqueue.php' );
		require( WP_ROCKET_FRONT_PATH . 'dns-prefetch.php' );

		if ( get_rocket_option( 'deferred_js_files' ) || get_rocket_option( 'defer_all_js' ) ) {
			require( WP_ROCKET_FRONT_PATH . 'deferred-js.php' );
		}

		if ( get_rocket_option( 'async_css' ) ) {
	        require( WP_ROCKET_FRONT_PATH . 'async-css.php' );
		}

		// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
		if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
			require( WP_ROCKET_FRONT_PATH . 'lazyload.php' );
		}

		require( WP_ROCKET_FRONT_PATH . 'protocol.php' );
	}

	// You can hook this to trigger any action when WP Rocket is correctly loaded, so, not in AUTOSAVE mode.
	if ( rocket_valid_key() ) {
		/**
		 * Fires when WP Rocket is correctly loaded
		 *
		 * @since 1.0
		*/
		do_action( 'wp_rocket_loaded' );
	}
}
add_action( 'plugins_loaded', 'rocket_init' );

/**
 * Tell WP what to do when plugin is deactivated.
 *
 * @since 1.0
 */
function rocket_deactivation() {
	if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( $_GET['rocket_nonce'], 'force_deactivation' ) ) {
	  	global $is_apache;
		$causes = array();

		// .htaccess problem.
		if ( $is_apache && ! rocket_direct_filesystem()->is_writable( get_home_path() . '.htaccess' ) ) {
			$causes[] = 'htaccess';
		}

		// wp-config problem.
		if ( ! rocket_direct_filesystem()->is_writable( rocket_find_wpconfig_path() ) ) {
			$causes[] = 'wpconfig';
		}

		if ( count( $causes ) ) {
	        set_transient( $GLOBALS['current_user']->ID . '_donotdeactivaterocket', $causes );
	        wp_safe_redirect( wp_get_referer() );
	        die();
		}
	}

	// Delete config files.
	rocket_delete_config_file();

	if ( ! count( glob( WP_ROCKET_CONFIG_PATH . '*.php' ) ) ) {
		// Delete All WP Rocket rules of the .htaccess file.
	    flush_rocket_htaccess( true );

	    // Remove WP_CACHE constant in wp-config.php.
	    set_rocket_wp_cache_define( false );

	    // Delete content of advanced-cache.php.
	    rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
	}

	// Update customer key & licence.
	wp_remote_get( WP_ROCKET_WEB_API . 'pause-licence.php', array( 'blocking' => false ) );

	delete_transient( 'rocket_check_licence_30' );
	delete_transient( 'rocket_check_licence_1' );
	delete_site_transient( 'update_wprocket_response' );
}
register_deactivation_hook( __FILE__, 'rocket_deactivation' );

/**
 * Tell WP what to do when plugin is activated.
 *
 * @since 1.1.0
 */
function rocket_activation() {
	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	if ( defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
		require( WP_ROCKET_INC_PATH . 'domain-mapping.php' );
	}

	require( WP_ROCKET_FUNCTIONS_PATH . 'options.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'files.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'formatting.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'plugins.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'i18n.php' );
	require( WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php' );
	require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php' );

    if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
    	require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php' );
    }

	if ( rocket_valid_key() ) {
	    // Add All WP Rocket rules of the .htaccess file.
	    flush_rocket_htaccess();

	    // Add WP_CACHE constant in wp-config.php.
		set_rocket_wp_cache_define( true );
	}

	// Create the cache folders (wp-rocket & min).
	rocket_init_cache_dir();

	// Create the config folder (wp-rocket-config).
	rocket_init_config_dir();

	// Create advanced-cache.php file.
	rocket_generate_advanced_cache_file();

	// Update customer key & licence.
	wp_remote_get( WP_ROCKET_WEB_API . 'activate-licence.php', array( 'blocking' => false ) );
}
register_activation_hook( __FILE__, 'rocket_activation' );
