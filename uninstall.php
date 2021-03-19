<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

require_once dirname( __FILE__ ) . '/inc/Engine/WPRocketUninstall.php';

// RUCSS Database Engine.
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/base.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/column.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/schema.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/query.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/row.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/table.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/Queries/meta.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/Queries/date.php';
require_once dirname( __FILE__ ) . '/inc/Dependencies/Database/Queries/compare.php';
require_once dirname( __FILE__ ) . '/inc/Engine/Optimization/RUCSS/Database/Tables/Resources.php';
require_once dirname( __FILE__ ) . '/inc/Engine/Optimization/RUCSS/Database/Tables/UsedCSS.php';

$rocket_rucss_resources_table = new WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources();
$rocket_rucss_usedcss_table   = new WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS();

$rocket_uninstall = new WPRocketUninstall( WP_ROCKET_CACHE_ROOT_PATH, WP_ROCKET_CONFIG_PATH, $rocket_rucss_resources_table, $rocket_rucss_usedcss_table );
$rocket_uninstall->uninstall();
