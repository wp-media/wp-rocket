<?php
/**
 * Initializes the wp-media/phpunit handler, which then calls the rocket integration test suite.
 */

define( 'WPMEDIA_PHPUNIT_ROOT_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WPMEDIA_PHPUNIT_ROOT_TEST_DIR', __DIR__ );

// TEMP DEBUG (remove after diagnosis): list every included file that lacks a PHP
// open tag — those are the files Patchwork echoes reinstateWrapper() for.
register_shutdown_function(
	function () {
		foreach ( get_included_files() as $file ) {
			$contents = @file_get_contents( $file );
			if ( false === $contents ) {
				continue;
			}
			if ( false === strpos( $contents, '<?php' ) && false === strpos( $contents, '<?=' ) ) {
				fwrite( STDOUT, 'NOOPEN-INCLUDED: ' . $file . "\n" );
			}
		}
	}
);

require_once WPMEDIA_PHPUNIT_ROOT_DIR . 'vendor/wp-media/phpunit/Integration/bootstrap.php';

define( 'WPMEDIA_IS_TESTING', true ); // Used by wp-media/{package}.
