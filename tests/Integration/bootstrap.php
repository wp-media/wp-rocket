<?php

namespace WP_Rocket\Tests\Integration;

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
