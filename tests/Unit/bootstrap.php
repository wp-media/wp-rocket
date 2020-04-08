<?php

namespace WP_Rocket\Tests\Unit;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

// Set the path and URL to our virtual filesystem.
define( 'WP_ROCKET_CACHE_ROOT_PATH', 'vfs://public/wp-content/cache/' );
define( 'WP_ROCKET_CACHE_ROOT_URL', 'vfs://public/wp-content/cache/' );

/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit
 * tests start.
 *
 * @since 3.5
 */
function load_original_functions_before_mocking() {
	$originals = [
		'inc/constants.php',
		'inc/functions/api.php',
		'inc/functions/files.php',
		'inc/functions/formatting.php',
		'inc/functions/i18n.php',
		'inc/functions/options.php',
		'inc/functions/posts.php',
	];

	foreach ( $originals as $file ) {
		require_once WP_ROCKET_PLUGIN_ROOT . $file;
	}
}

load_original_functions_before_mocking();
