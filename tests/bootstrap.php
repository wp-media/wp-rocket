<?php

$GLOBALS['wp_tests_options'][ 'template' ] = 'twentyseventeen';
$GLOBALS['wp_tests_options'][ 'stylesheet' ] = 'twentyseventeen';
$GLOBALS['wp_tests_options'][ 'active_plugins' ][] = basename( dirname( __DIR__ ) ) .'/action-scheduler.php';

// Check for select constants defined as environment variables
foreach ( array('WP_CONTENT_DIR', 'WP_CONTENT_URL', 'WP_PLUGIN_DIR', 'WP_PLUGIN_URL', 'WPMU_PLUGIN_DIR') as $env_constant ) {
	if ( false !== getenv( $env_constant ) && !defined( $env_constant ) ) {
		define( $env_constant, getenv( $env_constant ));
	}
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', dirname( dirname( dirname( __FILE__ ) ) ) );
}

$wordpress_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : sys_get_temp_dir() . '/wordpress-tests-lib';
require_once $wordpress_tests_dir . '/includes/functions.php';
require $wordpress_tests_dir . '/includes/bootstrap.php';

require_once dirname(dirname( __FILE__ ) ) .'/action-scheduler.php';

if ( class_exists( 'PHPUnit\Framework\TestResult' ) ) { // PHPUnit 6.0 or newer
	include_once('ActionScheduler_UnitTestCase.php');
} else {
	include_once('phpunit/deprecated/ActionScheduler_UnitTestCase.php');
}

include_once('phpunit/ActionScheduler_Mocker.php');
include_once('phpunit/ActionScheduler_Mock_Async_Request_QueueRunner.php');
