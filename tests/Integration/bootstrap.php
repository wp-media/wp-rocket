<?php
/**
 * Bootstraps the WP Rocket Plugin integration tests
 *
 * @package WP_Rocket\Tests\Integration
 */

namespace WP_Rocket\Tests\Integration;

use function WP_Rocket\Tests\init_test_suite;

require_once dirname( dirname( __FILE__ ) ) . '/boostrap-functions.php';
init_test_suite( 'Integration' );

/**
 * Gets the WP tests suite directory
 *
 * @return string
 */
function WPRocketPluginGetWPTestsDir() {
	$tests_dir = getenv( 'WP_TESTS_DIR' );

	// Travis CI & Vagrant SSH tests directory.
	if ( empty( $tests_dir ) ) {
		$tests_dir = '/tmp/wordpress-tests-lib';
	}
	// If the tests' includes directory does not exist, try a relative path to Core tests directory.
	if ( ! file_exists( $tests_dir . '/includes/' ) ) {
		$tests_dir = '../../../../tests/phpunit';
	}
	// Check it again. If it doesn't exist, stop here and post a message as to why we stopped.
	if ( ! file_exists( $tests_dir . '/includes/' ) ) {
		trigger_error( 'Unable to run the integration tests, as the WordPress test suite could not be located.', E_USER_ERROR );  // @codingStandardsIgnoreLine.
	}

	// Strip off the trailing directory separator, if it exists.
	return rtrim( $tests_dir, DIRECTORY_SEPARATOR );
}

$rocket_tests_dir = WPRocketPluginGetWPTestsDir();

// Give access to tests_add_filter() function.
require_once $rocket_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function rocket_manually_load_plugin() {
	require WP_ROCKET_PLUGIN_ROOT . '/wp-rocket.php';
}

tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\rocket_manually_load_plugin' );

require_once $rocket_tests_dir . '/includes/bootstrap.php';

unset( $rocket_tests_dir );
