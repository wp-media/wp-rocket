<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

require_once dirname( __FILE__ ) . '/inc/classes/WP_Rocket_Uninstall.php';

$rocket_uninstall = new WP_Rocket_Uninstall( WP_ROCKET_CACHE_ROOT_PATH, WP_ROCKET_CONFIG_PATH );
$rocket_uninstall->uninstall();
