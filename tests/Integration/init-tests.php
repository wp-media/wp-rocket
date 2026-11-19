<?php
/**
 * Initializes the wp-media/phpunit handler, which then calls the rocket integration test suite.
 */

define( 'WPMEDIA_PHPUNIT_ROOT_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WPMEDIA_PHPUNIT_ROOT_TEST_DIR', __DIR__ );

require_once WPMEDIA_PHPUNIT_ROOT_DIR . 'vendor/wp-media/phpunit/src/Integration/bootstrap.php';

// The bootstrap's _delete_all_posts() purges query string URLs, which loads and caches the dynamic lists outside any test.
delete_transient( 'wpr_dynamic_lists' );

define( 'WPMEDIA_IS_TESTING', true ); // Used by wp-media/{package}.
