<?php
/**
 * Initializes the wp-media/phpunit handler, which then calls the rocket integration test suite.
 */

define( 'WPMEDIA_PHPUNIT_ROOT_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WPMEDIA_PHPUNIT_ROOT_TEST_DIR', __DIR__ );

require_once WPMEDIA_PHPUNIT_ROOT_DIR . 'vendor/wp-media/phpunit/Integration/bootstrap.php';

define( 'WPMEDIA_IS_TESTING', true ); // Used by wp-media/{package}.
