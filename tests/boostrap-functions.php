<?php
/**
 * Common bootstrap functionality.
 *
 * @package WP_Rocket\Tests
 */

namespace WP_Rocket\Tests;

/**
 * Initialize the test suite.
 *
 * @param string $test_suite Directory name of the test suite. Default is 'Unit'.
 */
function init_test_suite( $test_suite = 'Unit' ) {
	check_readiness();

	init_constants( $test_suite );

	// Load the Composer autoloader.
	require_once WP_ROCKET_PLUGIN_ROOT . '/vendor/autoload.php';

	// Load Patchwork before everything else in order to allow us to redefine WordPress, 3rd party, and WP Rocket functions.
	require_once WP_ROCKET_PLUGIN_ROOT . '/vendor/antecedent/patchwork/Patchwork.php';
}

/**
 * Check the system's readiness to run the tests.
 */
function check_readiness() {
	if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
		trigger_error( 'WP Rocket Unit Tests require PHP 5.6 or higher.', E_USER_ERROR ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Valid use case for our testing suite.
	}

	if ( ! file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
		trigger_error( 'Whoops, we need Composer before we start running tests.  Please type: `composer install`.  When done, try running `phpunit` again.', E_USER_ERROR ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Valid use case for our testing suite.
	}
}

/**
 * Initialize the constants.
 *
 * @param string $test_suite_folder Directory name of the test suite.
 */
function init_constants( $test_suite_folder ) {
	define( 'WP_ROCKET_PLUGIN_ROOT', dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
	define( 'WP_ROCKET_PLUGIN_TESTS_ROOT', __DIR__ . DIRECTORY_SEPARATOR . $test_suite_folder );

	if ( 'Unit' === $test_suite_folder && ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', WP_ROCKET_PLUGIN_ROOT );
	}
}
