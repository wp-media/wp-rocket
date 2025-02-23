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
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Base.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Column.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Schema.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Query.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Row.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Table.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Queries/Meta.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Queries/Date.php';
require_once __DIR__ . '/inc/Dependencies/BerlinDB/Database/Queries/Compare.php';
require_once __DIR__ . '/inc/Engine/Common/Database/TableInterface.php';
require_once __DIR__ . '/inc/Engine/Common/Database/Tables/AbstractTable.php';
require_once __DIR__ . '/inc/Engine/Optimization/RUCSS/Database/Tables/UsedCSS.php';
require_once __DIR__ . '/inc/Engine/Preload/Database/Tables/Cache.php';
require_once __DIR__ . '/inc/Engine/Common/PerformanceHints/Database/Table/TableInterface.php';
require_once __DIR__ . '/inc/Engine/Common/PerformanceHints/Database/Table/AbstractTable.php';
require_once __DIR__ . '/inc/Engine/Media/AboveTheFold/Database/Tables/AboveTheFold.php';
require_once __DIR__ . '/inc/Engine/Optimization/LazyRenderContent/Database/Table/LazyRenderContent.php';
require_once __DIR__ . '/inc/Engine/Media/PreloadFonts/Database/Table/PreloadFonts.php';
require_once __DIR__ . '/inc/Engine/Media/PreconnectExternalDomains/Database/Table/PreconnectExternalDomains.php';

$rocket_rucss_usedcss_table   = new WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS();
$rocket_cache_table           = new WP_Rocket\Engine\Preload\Database\Tables\Cache();
$rocket_atf_table             = new WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold();
$rocket_lrc_table             = new WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Table\LazyRenderContent();
$rocket_preload_fonts_table   = new WP_Rocket\Engine\Media\PreloadFonts\Database\Table\PreloadFonts();
$rocket_preload_domains_table = new WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Table\PreconnectExternalDomains();
$rocket_uninstall             = new WPRocketUninstall(
	WP_ROCKET_CACHE_ROOT_PATH,
	WP_ROCKET_CONFIG_PATH,
	$rocket_rucss_usedcss_table,
	$rocket_cache_table,
	$rocket_atf_table,
	$rocket_lrc_table,
	$rocket_preload_fonts_table,
	$rocket_preload_domains_table
	);

$rocket_uninstall->uninstall();
