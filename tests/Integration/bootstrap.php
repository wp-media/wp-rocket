<?php

namespace WP_Rocket\Tests\Integration;

use WC_Install;
use WPMedia\PHPUnit\BootstrapManager;
use function Patchwork\redefine;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

// Manually load the plugin being tested.
tests_add_filter(
	'muplugins_loaded',
	function() {
		if ( BootstrapManager::isGroup( 'WithSCCSS' ) ) {
			// Load Simple Custom CSS plugin.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/simple-custom-css/simple-custom-css.php';
			update_option(
				'sccss_settings',
				[
					'sccss-content' => '.simple-custom-css { color: red; }',
				]
			);
		}

		if ( BootstrapManager::isGroup( 'WithAmp' ) ) {
			// Load AMP plugin.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/amp/amp.php';
		}

		if ( BootstrapManager::isGroup( 'WithAmpAndCloudflare' ) ) {
			// Load AMP plugin.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/amp/amp.php';
			update_option(
				'wp_rocket_settings',
				[
					'do_cloudflare'               => 1,
					'cloudflare_protocol_rewrite' => 1,
				]
			);
		}

		// Set the path and URL to our virtual filesystem.
		define( 'WP_ROCKET_CACHE_ROOT_PATH', 'vfs://public/wp-content/cache/' );
		define( 'WP_ROCKET_CACHE_ROOT_URL', 'http://example.org/wp-content/cache/' );

		if ( BootstrapManager::isGroup( 'WithSmush' ) ) {
			// Load WP Smush.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/wp-smushit/wp-smush.php';
		}

		if ( BootstrapManager::isGroup( 'WithWoo' ) ) {
			// Load WooCommerce.
			define( 'WC_TAX_ROUNDING_MODE', 'auto' );
			define( 'WC_USE_TRANSACTIONS', false );
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/woocommerce/woocommerce/woocommerce.php';
		}

		if ( BootstrapManager::isGroup( 'BeaverBuilder' ) ) {
			define( 'FL_BUILDER_VERSION', '5.3' );
		}

		if ( BootstrapManager::isGroup( 'Elementor' ) ) {
			define( 'ELEMENTOR_VERSION', '2.0' );
		}

		if ( BootstrapManager::isGroup( 'Hummingbird' ) ) {
			define( 'WP_ADMIN', true );
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/hummingbird-performance/wp-hummingbird.php';
		}

		if ( BootstrapManager::isGroup( 'Cloudways' ) ) {
			$_SERVER['cw_allowed_ip'] = true;
		}

		if ( BootstrapManager::isGroup( 'SpinUpWP' ) ) {
			putenv( 'SPINUPWP_CACHE_PATH=/wp-content/spinupwp-cache/' );
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/spinupwp/spinupwp.php';
		}

		if ( BootstrapManager::isGroup( 'O2Switch' ) ) {
			define( 'O2SWITCH_VARNISH_PURGE_KEY', 'test' );
		}

		if ( BootstrapManager::isGroup( 'WordPressCom' ) ) {
			define( 'WPCOMSH_VERSION', '1.0' );
		}

		if ( BootstrapManager::isGroup( 'PDFEmbedder' ) ) {
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/pdf-embedder/pdf_embedder.php';
		}

		if ( BootstrapManager::isGroup( 'PDFEmbedderPremium' ) ) {
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/pdf-embedder/core/core_pdf_embedder.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/PDFEmbedder/mobile_pdf_embedder.php';
		}

		if ( BootstrapManager::isGroup( 'PDFEmbedderSecure' ) ) {
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/pdf-embedder/core/core_pdf_embedder.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/PDFEmbedder/secure_pdf_embedder.php';
		}

		// Overload the license key for testing.
		redefine( 'rocket_valid_key', '__return_true' );

		if ( BootstrapManager::isGroup( 'DoCloudflare' ) ) {
			update_option( 'wp_rocket_settings', [ 'do_cloudflare' => 1 ] );
		}

		if ( BootstrapManager::isGroup( 'WPEngine' ) ) {
			define( 'WP_ADMIN', true );
			define( 'PWP_NAME', 'PWP_NAME' );
			// Load WP Engine mocked files.
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/wpe_param.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/WPEngine/WpeCommon.php';
		}

		// Load the plugin.
		require WP_ROCKET_PLUGIN_ROOT . '/wp-rocket.php';
	}
);


// install WC.
tests_add_filter(
	'setup_theme',
	function() {
		if ( ! BootstrapManager::isGroup( 'WithWoo' ) ) {
			return;
		}
		// Clean existing install first.
		define( 'WP_UNINSTALL_PLUGIN', true );
		define( 'WC_REMOVE_ALL_DATA', true );
		include WP_ROCKET_PLUGIN_ROOT . '/vendor/woocommerce/woocommerce/uninstall.php';

		WC_Install::install();

		// Reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374.
		if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
			$GLOBALS['wp_roles']->reinit();
		} else {
			$GLOBALS['wp_roles'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			wp_roles();
		}

		echo esc_html( 'Installing WooCommerce...' . PHP_EOL );
	}
);
