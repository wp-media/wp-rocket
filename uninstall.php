<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) {
	define( 'WP_ROCKET_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
}

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

require_once __DIR__ . '/inc/Engine/WPRocketUninstall.php';

// RUCSS Database Engine.
require_once __DIR__ . '/inc/Dependencies/Database/Base.php';
require_once __DIR__ . '/inc/Dependencies/Database/Column.php';
require_once __DIR__ . '/inc/Dependencies/Database/Schema.php';
require_once __DIR__ . '/inc/Dependencies/Database/Query.php';
require_once __DIR__ . '/inc/Dependencies/Database/Row.php';
require_once __DIR__ . '/inc/Dependencies/Database/Table.php';
require_once __DIR__ . '/inc/Dependencies/Database/Queries/Meta.php';
require_once __DIR__ . '/inc/Dependencies/Database/Queries/Date.php';
require_once __DIR__ . '/inc/Dependencies/Database/Queries/Compare.php';
require_once __DIR__ . '/inc/Engine/Common/Database/Tables/AbstractTable.php';
require_once __DIR__ . '/inc/Engine/Optimization/RUCSS/Database/Tables/UsedCSS.php';
require_once __DIR__ . '/inc/Engine/Preload/Database/Tables/Cache.php';
require_once __DIR__ . '/inc/Engine/Media/AboveTheFold/Database/Tables/AboveTheFold.php';

$rocket_rucss_usedcss_table = new WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS();
$rocket_cache_table         = new WP_Rocket\Engine\Preload\Database\Tables\Cache();
$rocket_atf_table           = new WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold();
$rocket_uninstall           = new WPRocketUninstall(
	WP_ROCKET_CACHE_ROOT_PATH,
	WP_ROCKET_CONFIG_PATH,
	$rocket_rucss_usedcss_table,
	$rocket_cache_table,
	$rocket_atf_table
	);

$rocket_uninstall->uninstall();
