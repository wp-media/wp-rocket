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
		'rocket_get_constant'        => WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php',
		'rocket_is_live_site'        => WP_ROCKET_PLUGIN_ROOT . 'inc/functions/api.php',
		'rocket_direct_filesystem'   => WP_ROCKET_PLUGIN_ROOT . 'inc/functions/files.php',
		'rocket_add_url_protocol'    => WP_ROCKET_PLUGIN_ROOT . 'inc/functions/formatting.php',
		'get_rocket_option'          => WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php',
		'get_rocket_post_dates_urls' => WP_ROCKET_PLUGIN_ROOT . 'inc/functions/posts.php',
	];

	foreach ( $originals as $function_name => $file ) {
		if ( ! function_exists( $function_name ) ) {
			require_once $file;
		}
	}
}

load_original_functions_before_mocking();
