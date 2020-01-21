<?php
/**
 * Bootstraps the WP Rocket Plugin Unit Tests
 *
 * @package WP_Rocket\Tests\Unit
 */

namespace WP_Rocket\Tests\Unit;

use function WP_Rocket\Tests\init_test_suite;

require_once dirname( dirname( __FILE__ ) ) . '/boostrap-functions.php';
init_test_suite( 'Unit' );

define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );

/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit tests start.
 *
 * @since 1.0.0
 */
function load_original_functions_before_mocking() {
	$originals = [
		'rocket_get_constant' => require_once WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php',
	];

	foreach ( $originals as $function_name => $file ) {
		if ( ! function_exists( $function_name ) ) {
			require_once $file;
		}
	}
}

load_original_functions_before_mocking();
