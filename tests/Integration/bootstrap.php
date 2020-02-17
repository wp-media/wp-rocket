<?php

namespace WP_Rocket\Tests\Integration;

use function Patchwork\redefine;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );
define( 'WPMEDIA_IS_TESTING', true ); // Used by wp-media/{package}.
define( 'WP_ROCKET_ADVANCED_CACHE', true );

// Manually load the plugin being tested.
tests_add_filter(
	'muplugins_loaded',
	function() {
		// Load WooCommerce.
		require WP_ROCKET_PLUGIN_ROOT . '/vendor/woocommerce/woocommerce/woocommerce.php';

		// Overload the license key for testing.
		redefine( 'rocket_valid_key', '__return_true' );

		// Load the plugin.
		require WP_ROCKET_PLUGIN_ROOT . '/wp-rocket.php';
	}
);
