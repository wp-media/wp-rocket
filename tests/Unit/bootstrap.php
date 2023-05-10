<?php

namespace WP_Rocket\Tests\Unit;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

// Set the path and URL to our virtual filesystem.
define( 'WP_ROCKET_CACHE_ROOT_PATH', 'vfs://public/wp-content/cache/' );
define( 'WP_ROCKET_CACHE_ROOT_URL', 'vfs://public/wp-content/cache/' );
define( 'OBJECT', 'OBJECT' );
/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit
 * tests start.
 *
 * @since 3.5
 */
function load_original_files_before_mocking() {
	$originals = [
		'inc/constants.php',
		'inc/functions/api.php',
		'inc/functions/files.php',
		'inc/functions/formatting.php',
		'inc/functions/i18n.php',
		'inc/functions/options.php',
		'inc/functions/posts.php',
		'inc/functions/htaccess.php',
		'inc/functions/admin.php',
	];
	foreach ( $originals as $file ) {
		require_once WP_ROCKET_PLUGIN_ROOT . $file;
	}

	$fixtures = [
		'/WP_Error.php',
		'/WP.php',
		'/WP_Theme.php',
		'/WPDieException.php',
		'/WP_Sitemaps_Index.php',
		'/WP_Sitemaps.php',
		'/WP_Filesystem_Direct.php',
		'/Action_Scheduler/ActionScheduler_Abstract_QueueRunner.php',
		'/Kinsta_Cache.php',
		'/WP_Rewrite.php',
		'/inc/ThirdParty/Plugins/SEO/RankMathSEO/fixtures.php',
		'/inc/ThirdParty/Plugins/SEO/TheSEOFramework/fixtures.php',
    ];
	foreach ( $fixtures as $file ) {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . $file;
	}
}

load_original_files_before_mocking();
require_once WP_ROCKET_PLUGIN_ROOT . 'inc/compat.php';
