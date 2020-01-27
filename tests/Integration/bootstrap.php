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
 * Get the WordPress' tests suite directory.
 *
 * @return string Returns The directory path to the WordPress testing environment.
 */
function get_wp_tests_dir() {
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
		trigger_error( 'Unable to run the integration tests, because the WordPress test suite could not be located.', E_USER_ERROR ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Valid use case for our testing suite.
	}

	// Strip off the trailing directory separator, if it exists.
	return rtrim( $tests_dir, DIRECTORY_SEPARATOR );
}

/**
 * Bootstraps the integration testing environment with WordPress and WP Rocket.
 *
 * @param string $wp_tests_dir The directory path to the WordPress testing environment.
 */
function bootstrap_integration_suite( $wp_tests_dir ) {
	// Give access to tests_add_filter() function.
	require_once $wp_tests_dir . '/includes/functions.php';

	// Manually load the plugin being tested.
	tests_add_filter(
		'muplugins_loaded',
		function() {
			// Load WooCommerce.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/woocommerce/woocommerce/woocommerce.php';
			// Overload the license key for testing.
			\Patchwork\redefine( 'rocket_valid_key', '__return_true' );

			// Load the plugin.
			require WP_ROCKET_PLUGIN_ROOT . '/wp-rocket.php';
		}
	);

	// Start up the WP testing environment.
	require_once $wp_tests_dir . '/includes/bootstrap.php';
}

bootstrap_integration_suite( get_wp_tests_dir() );
