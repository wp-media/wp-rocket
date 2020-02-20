<?php

namespace WP_Rocket\Tests\Unit;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit
 * tests start.
 *
 * @since 3.5
 */
function load_original_functions_before_mocking() {
	$originals = [
		'rocket_get_constant' => WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php',
	];

	foreach ( $originals as $function_name => $file ) {
		if ( ! function_exists( $function_name ) ) {
			require_once $file;
		}
	}
}

load_original_functions_before_mocking();
