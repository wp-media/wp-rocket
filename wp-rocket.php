<?php
/**
 * Plugin Name: WP Rocket
 * Plugin URI: https://wp-rocket.me
 * Description: The best WordPress performance plugin.
 * Version: 3.2.6
 * Code Name: Dagobah
 * Author: WP Media
 * Author URI: http://wp-media.me
 * Licence: GPLv2 or later
 *
 * Text Domain: rocket
 * Domain Path: languages
 *
 * Copyright 2013-2018 WP Rocket
 * */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Rocket defines.
define( 'WP_ROCKET_VERSION',               '3.2.6' );
define( 'WP_ROCKET_WP_VERSION',            '4.7' );
define( 'WP_ROCKET_PHP_VERSION',           '5.4' );
define( 'WP_ROCKET_PRIVATE_KEY',           false );
define( 'WP_ROCKET_SLUG',                  'wp_rocket_settings' );
define( 'WP_ROCKET_WEB_MAIN',              'https://wp-rocket.me/' );
define( 'WP_ROCKET_WEB_API',               WP_ROCKET_WEB_MAIN . 'api/wp-rocket/' );
define( 'WP_ROCKET_WEB_CHECK',             WP_ROCKET_WEB_MAIN . 'check_update.php' );
define( 'WP_ROCKET_WEB_VALID',             WP_ROCKET_WEB_MAIN . 'valid_key.php' );
define( 'WP_ROCKET_WEB_INFO',              WP_ROCKET_WEB_MAIN . 'plugin_information.php' );
define( 'WP_ROCKET_BOT_URL',               'http://bot.wp-rocket.me/launch.php' );
define( 'WP_ROCKET_FILE',                  __FILE__ );
define( 'WP_ROCKET_PATH',                  realpath( plugin_dir_path( WP_ROCKET_FILE ) ) . '/' );
define( 'WP_ROCKET_INC_PATH',              realpath( WP_ROCKET_PATH . 'inc/' ) . '/' );
define( 'WP_ROCKET_DEPRECATED_PATH',       realpath( WP_ROCKET_INC_PATH . 'deprecated/' ) . '/' );
define( 'WP_ROCKET_FRONT_PATH',            realpath( WP_ROCKET_INC_PATH . 'front/' ) . '/' );
define( 'WP_ROCKET_ADMIN_PATH',            realpath( WP_ROCKET_INC_PATH . 'admin' ) . '/' );
define( 'WP_ROCKET_ADMIN_UI_PATH',         realpath( WP_ROCKET_ADMIN_PATH . 'ui' ) . '/' );
define( 'WP_ROCKET_ADMIN_UI_MODULES_PATH', realpath( WP_ROCKET_ADMIN_UI_PATH . 'modules' ) . '/' );
define( 'WP_ROCKET_COMMON_PATH',           realpath( WP_ROCKET_INC_PATH . 'common' ) . '/' );
define( 'WP_ROCKET_FUNCTIONS_PATH',        realpath( WP_ROCKET_INC_PATH . 'functions' ) . '/' );
define( 'WP_ROCKET_VENDORS_PATH',          realpath( WP_ROCKET_INC_PATH . 'vendors' ) . '/' );
define( 'WP_ROCKET_3RD_PARTY_PATH',        realpath( WP_ROCKET_INC_PATH . '3rd-party' ) . '/' );
define( 'WP_ROCKET_CONFIG_PATH',           WP_CONTENT_DIR . '/wp-rocket-config/' );
define( 'WP_ROCKET_URL',                   plugin_dir_url( WP_ROCKET_FILE ) );
define( 'WP_ROCKET_INC_URL',               WP_ROCKET_URL . 'inc/' );
define( 'WP_ROCKET_FRONT_URL',             WP_ROCKET_INC_URL . 'front/' );
define( 'WP_ROCKET_FRONT_JS_URL',          WP_ROCKET_FRONT_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_URL',             WP_ROCKET_INC_URL . 'admin/' );
define( 'WP_ROCKET_ADMIN_UI_URL',          WP_ROCKET_ADMIN_URL . 'ui/' );
define( 'WP_ROCKET_ADMIN_UI_JS_URL',       WP_ROCKET_ADMIN_UI_URL . 'js/' );
define( 'WP_ROCKET_ADMIN_UI_CSS_URL',      WP_ROCKET_ADMIN_UI_URL . 'css/' );
define( 'WP_ROCKET_ADMIN_UI_IMG_URL',      WP_ROCKET_ADMIN_UI_URL . 'img/' );
define( 'WP_ROCKET_ASSETS_URL',            WP_ROCKET_URL . 'assets/' );
define( 'WP_ROCKET_ASSETS_JS_URL',         WP_ROCKET_ASSETS_URL . 'js/' );
define( 'WP_ROCKET_ASSETS_CSS_URL',        WP_ROCKET_ASSETS_URL . 'css/' );
define( 'WP_ROCKET_ASSETS_IMG_URL',        WP_ROCKET_ASSETS_URL . 'img/' );

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}
define( 'WP_ROCKET_CACHE_PATH',         WP_ROCKET_CACHE_ROOT_PATH . 'wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_PATH',  WP_ROCKET_CACHE_ROOT_PATH . 'min/' );
define( 'WP_ROCKET_CACHE_BUSTING_PATH', WP_ROCKET_CACHE_ROOT_PATH . 'busting/' );
define( 'WP_ROCKET_CRITICAL_CSS_PATH',  WP_ROCKET_CACHE_ROOT_PATH . 'critical-css/' );

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_URL' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_URL', WP_CONTENT_URL . '/cache/' );
}
define( 'WP_ROCKET_CACHE_URL',         WP_ROCKET_CACHE_ROOT_URL . 'wp-rocket/' );
define( 'WP_ROCKET_MINIFY_CACHE_URL',  WP_ROCKET_CACHE_ROOT_URL . 'min/' );
define( 'WP_ROCKET_CACHE_BUSTING_URL', WP_ROCKET_CACHE_ROOT_URL . 'busting/' );

if ( ! defined( 'CHMOD_WP_ROCKET_CACHE_DIRS' ) ) {
	define( 'CHMOD_WP_ROCKET_CACHE_DIRS', 0755 );
}
if ( ! defined( 'WP_ROCKET_LASTVERSION' ) ) {
	define( 'WP_ROCKET_LASTVERSION', '3.1.4' );
}

require WP_ROCKET_INC_PATH . 'compat.php';
require dirname( __FILE__ ) . '/licence-data.php';
require WP_ROCKET_INC_PATH . 'classes/class-wp-rocket-requirements-check.php';

/**
 * Loads WP Rocket translations
 *
 * @since 3.0
 * @author Remy Perona
 *
 * @return void
 */
function rocket_load_textdomain() {
	// Load translations from the languages directory.
	$locale = get_locale();

	// This filter is documented in /wp-includes/l10n.php.
	$locale = apply_filters( 'plugin_locale', $locale, 'rocket' );
	load_textdomain( 'rocket', WP_LANG_DIR . '/plugins/wp-rocket-' . $locale . '.mo' );

	load_plugin_textdomain( 'rocket', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rocket_load_textdomain' );

$wp_rocket_requirement_checks = new WP_Rocket_Requirements_Check(
	array(
		'plugin_name'         => 'WP Rocket',
		'plugin_file'         => WP_ROCKET_FILE,
		'plugin_version'      => WP_ROCKET_VERSION,
		'plugin_last_version' => WP_ROCKET_LASTVERSION,
		'wp_version'          => WP_ROCKET_WP_VERSION,
		'php_version'         => WP_ROCKET_PHP_VERSION,
	)
);

if ( $wp_rocket_requirement_checks->check() ) {
	require WP_ROCKET_INC_PATH . 'main.php';
}

unset( $wp_rocket_requirement_checks );
