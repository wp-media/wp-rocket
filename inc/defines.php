<?php
if ( defined( 'WP_ROCKET_VERSION' ) ) {
	return;
}

// Base rocket defines.
define( 'WP_ROCKET_VERSION', '2.11.3' );
define( 'WP_ROCKET_PRIVATE_KEY', FALSE );
define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
define( 'WP_ROCKET_WEB_MAIN' ) or define( 'WP_ROCKET_WEB_MAIN', FALSE );
define( 'WP_ROCKET_WEB_API', WP_ROCKET_WEB_MAIN . 'api/wp-rocket/' );
define( 'WP_ROCKET_WEB_CHECK', WP_ROCKET_WEB_MAIN . 'check_update.php' );
define( 'WP_ROCKET_WEB_VALID', WP_ROCKET_WEB_MAIN . 'valid_key.php' );
define( 'WP_ROCKET_WEB_INFO', WP_ROCKET_WEB_MAIN . 'plugin_information.php' );
define( 'WP_ROCKET_BOT_URL', 'http://bot.wp-rocket.me/launch.php' );
define( 'WP_ROCKET_FILE', dirname( __DIR__ ) . '/wp-rocket.php' );
define( 'WP_ROCKET_PATH', rtrim( realpath( dirname( __DIR__ ) ), '/\\' ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_INC_PATH', WP_ROCKET_PATH . 'inc' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_FRONT_PATH', WP_ROCKET_INC_PATH . 'front' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_ADMIN_PATH', WP_ROCKET_INC_PATH . 'admin' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_ADMIN_UI_PATH', WP_ROCKET_ADMIN_PATH . 'ui' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_ADMIN_UI_MODULES_PATH', WP_ROCKET_ADMIN_UI_PATH . 'modules' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_COMMON_PATH', WP_ROCKET_INC_PATH . 'common' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_FUNCTIONS_PATH', WP_ROCKET_INC_PATH . 'functions' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_VENDORS_PATH', WP_ROCKET_INC_PATH . 'vendors' . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_3RD_PARTY_PATH', WP_ROCKET_INC_PATH . '3rd-party' . DIRECTORY_SEPARATOR );
