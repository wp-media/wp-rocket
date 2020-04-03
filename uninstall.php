<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

require_once dirname( __FILE__ ) . '/inc/Engine/WPRocketUninstall.php';

$rocket_uninstall = new WPRocketUninstall( WP_ROCKET_CACHE_ROOT_PATH, WP_ROCKET_CONFIG_PATH );
$rocket_uninstall->uninstall();
