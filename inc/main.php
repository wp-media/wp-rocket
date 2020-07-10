<?php

use League\Container\Container;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Plugin;
use WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber;

defined( 'ABSPATH' ) || exit;

// Composer autoload.
if ( file_exists( WP_ROCKET_PATH . 'vendor/autoload.php' ) ) {
	require WP_ROCKET_PATH . 'vendor/autoload.php';
}

require_once WP_ROCKET_FUNCTIONS_PATH . 'files.php';

/**
 * Fix Cloudflare Flexible SSL redirect first
 *
 * @since  3.4.1
 * @author Soponar Cristina
 */
require WP_ROCKET_VENDORS_PATH . 'ip_in_range.php';
require WP_ROCKET_COMMON_PATH . 'cloudflare-flexible-ssl.php';

rocket_fix_cf_flexible_ssl();

/**
 * Tell WP what to do when plugin is loaded.
 *
 * @since 1.0
 */
function rocket_init() {
	// Nothing to do if autosave.
	if ( defined( 'DOING_AUTOSAVE' ) ) {
		return;
	}

	// Call defines and functions.
	require WP_ROCKET_FUNCTIONS_PATH . 'options.php';

	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	$wp_rocket = new Plugin(
		WP_ROCKET_PATH . 'views',
		new Container()
	);
	$wp_rocket->load();

	// Call defines and functions.
	require_once WP_ROCKET_FUNCTIONS_PATH . 'api.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'posts.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'admin.php';
	require WP_ROCKET_INC_PATH . '/API/preload.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';
	require WP_ROCKET_DEPRECATED_PATH . 'deprecated.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.2.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.3.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.4.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.5.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.6.php';
	require WP_ROCKET_3RD_PARTY_PATH . '3rd-party.php';
	require WP_ROCKET_COMMON_PATH . 'admin-bar.php';
	require WP_ROCKET_COMMON_PATH . 'emoji.php';
	require WP_ROCKET_COMMON_PATH . 'embeds.php';

	if ( rocket_valid_key() ) {
		require WP_ROCKET_COMMON_PATH . 'purge.php';

		if ( is_multisite() && defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
			require WP_ROCKET_INC_PATH . '/domain-mapping.php';
		}
	}

	if ( is_admin() ) {
		require WP_ROCKET_ADMIN_PATH . 'upgrader.php';
		require WP_ROCKET_ADMIN_PATH . 'options.php';
		require WP_ROCKET_ADMIN_PATH . 'admin.php';
		require WP_ROCKET_ADMIN_UI_PATH . 'enqueue.php';
		require WP_ROCKET_ADMIN_UI_PATH . 'notices.php';
		require WP_ROCKET_ADMIN_UI_PATH . 'meta-boxes.php';
	} elseif ( rocket_valid_key() ) {
		require WP_ROCKET_FRONT_PATH . 'cookie.php';
		require WP_ROCKET_FRONT_PATH . 'images.php';
		require WP_ROCKET_FRONT_PATH . 'dns-prefetch.php';

		if ( get_rocket_option( 'defer_all_js' ) ) {
			require WP_ROCKET_FRONT_PATH . 'deferred-js.php';
		}

		require WP_ROCKET_FRONT_PATH . 'protocol.php';
	}

	// You can hook this to trigger any action when WP Rocket is correctly loaded, so, not in AUTOSAVE mode.
	if ( rocket_valid_key() ) {
		/**
		 * Fires when WP Rocket is correctly loaded
		 *
		 * @since 1.0
		*/
		do_action( 'wp_rocket_loaded' );
	}
}
add_action( 'plugins_loaded', 'rocket_init' );

/**
 * Tell WP what to do when plugin is deactivated.
 *
 * @since 1.0
 */
function rocket_deactivation() {
	global $is_apache;

	$filesystem = rocket_direct_filesystem();
	$wp_cache   = new WPCache( $filesystem );

	if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['rocket_nonce'] ), 'force_deactivation' ) ) {
		$causes = [];

		// .htaccess problem.
		if ( $is_apache && ! $filesystem->is_writable( get_home_path() . '.htaccess' ) ) {
			$causes[] = 'htaccess';
		}

		// wp-config problem.
		if (
			! $wp_cache->find_wpconfig_path()
			&&
			// This filter is documented in inc/Engine/Cache/WPCache.php.
			(bool) apply_filters( 'rocket_set_wp_cache_constant', true )
		) {
			$causes[] = 'wpconfig';
		}

		if ( count( $causes ) ) {
			set_transient( get_current_user_id() . '_donotdeactivaterocket', $causes );
			wp_safe_redirect( wp_get_referer() );
			die();
		}
	}

	// Delete config files.
	rocket_delete_config_file();

	if ( ! count( glob( WP_ROCKET_CONFIG_PATH . '*.php' ) ) ) {
		// Delete All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess( true );

		// Remove WP_CACHE constant in wp-config.php.
		$wp_cache->set_wp_cache_constant( false );

		// Delete content of advanced-cache.php.
		rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
	}

	// Update customer key & licence.
	wp_remote_get(
		WP_ROCKET_WEB_API . 'pause-licence.php',
		[
			'blocking' => false,
		]
	);

	// Delete transients.
	delete_transient( 'rocket_check_licence_30' );
	delete_transient( 'rocket_check_licence_1' );
	delete_site_transient( 'update_wprocket_response' );

	// Unschedule WP Cron events.
	wp_clear_scheduled_hook( 'rocket_facebook_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_google_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_cache_dir_size_check' );

	/**
	 * WP Rocket deactivation.
	 *
	 * @since  3.1.5
	 * @author Grégory Viguier
	 */
	do_action( 'rocket_deactivation' );

	( new Capabilities_Subscriber() )->remove_rocket_capabilities();
}
register_deactivation_hook( WP_ROCKET_FILE, 'rocket_deactivation' );

/**
 * Tell WP what to do when plugin is activated.
 *
 * @since 1.1.0
 */
function rocket_activation() {
	( new Capabilities_Subscriber() )->add_rocket_capabilities();

	$filesystem = rocket_direct_filesystem();
	$wp_cache   = new WPCache( $filesystem );

	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	if ( defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
		require WP_ROCKET_INC_PATH . 'domain-mapping.php';
	}

	require WP_ROCKET_FUNCTIONS_PATH . 'options.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';

	if ( class_exists( 'WPaaS\Plugin' ) ) {
		require WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php';
	}
	if ( defined( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
		require WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php';
	}

	if ( rocket_valid_key() ) {
		// Add All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess();

		// Add WP_CACHE constant in wp-config.php.
		$wp_cache->set_wp_cache_constant( true );
	}

	// Create the cache folders (wp-rocket & min).
	rocket_init_cache_dir();

	// Create the config folder (wp-rocket-config).
	rocket_init_config_dir();

	// Create advanced-cache.php file.
	rocket_generate_advanced_cache_file( new AdvancedCache( WP_ROCKET_PATH . 'views/cache/', $filesystem ) );

	/**
	 * WP Rocket activation.
	 *
	 * @since  3.1.5
	 * @author Grégory Viguier
	 */
	do_action( 'rocket_activation' );

	// Update customer key & licence.
	wp_remote_get(
		WP_ROCKET_WEB_API . 'activate-licence.php',
		[
			'blocking' => false,
		]
	);

	wp_remote_get(
		home_url(),
		[
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'WP Rocket/Homepage Preload',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		]
	);
}
register_activation_hook( WP_ROCKET_FILE, 'rocket_activation' );
