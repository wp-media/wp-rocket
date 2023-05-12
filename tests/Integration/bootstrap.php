<?php

namespace WP_Rocket\Tests\Integration;

use WC_Install;
use WP_Rocket\Tests\Fixtures\Kinsta\Kinsta_Cache;
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
        if ( BootstrapManager::isGroup( 'TranslatePress' ) ) {
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/classes/TRP_Settings.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/classes/TRP_Url_Converter.php';
		}

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

		if ( BootstrapManager::isGroup('Kinsta') ) {
			$_SERVER['KINSTA_CACHE_ZONE'] = true ;
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/Kinsta_Cache.php';
			$GLOBALS['kinsta_cache'] = new Kinsta_Cache();
		}

		if ( BootstrapManager::isGroup( 'WithWoo' ) ) {
			// Load WooCommerce.
			define( 'WC_TAX_ROUNDING_MODE', 'auto' );
			define( 'WC_USE_TRANSACTIONS', false );
			require WP_ROCKET_PLUGIN_ROOT . 'vendor/wpackagist-plugin/woocommerce/woocommerce.php';
		}

		if ( BootstrapManager::isGroup( 'BeaverBuilder' ) ) {
			define( 'FL_BUILDER_VERSION', '5.3' );
		}

		if ( BootstrapManager::isGroup( 'Elementor' ) ) {
			define( 'ELEMENTOR_VERSION', '2.0' );
		}

		if ( BootstrapManager::isGroup( 'ConvertPlug' ) ) {
			define( 'CP_VERSION', '1.0' );
		}

		if ( BootstrapManager::isGroup( 'TheEventsCalendar' ) ) {
			define( 'TRIBE_EVENTS_FILE', true );
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

		if ( BootstrapManager::isGroup( 'Dreampress' ) ) {
			$_SERVER[ 'DH_USER'] = 'wp_74cgrq';
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

		if ( BootstrapManager::isGroup( 'WPXCloud' ) ) {
			$_SERVER[ 'HTTP_WPXCLOUD'] = true;
		}

		if ( BootstrapManager::isGroup( 'LiteSpeed' ) ) {
			$_SERVER[ 'X-LSCACHE'] = 'on';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/LiteSpeed/HeaderCollector.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/LiteSpeed/override_header_functions.php';
		}

		if ( BootstrapManager::isGroup( 'Godaddy' ) ) {
			// Load GoDaddy mocked files.
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Godaddy/Plugin.php';
		}

		if ( BootstrapManager::isGroup( 'RevolutionSlider' ) ) {
			define( 'RS_REVISION', '6.5.5' );
		}
		if ( BootstrapManager::isGroup( 'WordFence' ) ) {
			define( 'WORDFENCE_VERSION', '1' );
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Security/WordFence/wordfence.php';
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Security/WordFence/wfConfig.php';
		}

		if ( BootstrapManager::isGroup( 'RankMathSEO' ) ) {
			define('RANK_MATH_FILE', '1');
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/SEO/RankMathSEO/fixtures.php';
		}

		if ( BootstrapManager::isGroup( 'SEOPress' ) ) {
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/SEO/SEOPress/fixtures.php';
		}

		if ( BootstrapManager::isGroup( 'TheSEOFramework' ) ) {
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/SEO/TheSEOFramework/fixtures.php';
		}

		if ( BootstrapManager::isGroup( 'WPGeotargeting' ) ) {
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/WPGeotargeting/fixtures.php';
		}

		if ( BootstrapManager::isGroup( 'AllInOneSeoPack' ) ) {
			if(! defined('AIOSEOP_VERSION')) {
				define('AIOSEOP_VERSION', true);
			}
			if(! defined('AIOSEO_VERSION')) {
				define('AIOSEO_VERSION', true);
			}
		}

		if ( BootstrapManager::isGroup( 'Jetpack' ) ) {
			// Load AMP plugin.
			require WP_ROCKET_PLUGIN_ROOT . '/vendor/wpackagist-plugin/jetpack/jetpack.php';
			update_option(
				'jetpack_active_modules',
				[
					'sitemaps',
					'widgets',
				]
			);
			require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Jetpack/functions.php';
		}

		if ( BootstrapManager::isGroup( 'RocketLazyLoad' ) ) {
			define( 'ROCKET_LL_VERSION', '2.3.6' );
		}

		if ( BootstrapManager::isGroup( 'OneCom' ) ) {
			$_SERVER[ 'GROUPONE_BRAND_NAME'] = 'one.com';
			$_SERVER[ 'ONECOM_DOMAIN_NAME'] = 'example.com';
			$_SERVER[ 'HTTP_HOST'] = 'example.com';
		}

		if ( BootstrapManager::isGroup( 'Perfmatters' ) ) {
			define( 'PERFMATTERS_VERSION', '2.0.2' );
		}

		if ( BootstrapManager::isGroup( 'RapidLoad' ) ) {
			define( 'UUCSS_VERSION', '1.6.34' );
		}

		if ( BootstrapManager::isGroup( 'ProIsp' ) ) {
			$_SERVER[ 'GROUPONE_BRAND_NAME'] = 'proisp.no';
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
		include WP_ROCKET_PLUGIN_ROOT . 'vendor/wpackagist-plugin/woocommerce/uninstall.php';

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
