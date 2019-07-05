<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Composer autoload.
if ( file_exists( WP_ROCKET_PATH . 'vendor/autoload.php' ) ) {
	require WP_ROCKET_PATH . 'vendor/autoload.php';
}

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

	$wp_rocket = new WP_Rocket\Plugin( WP_ROCKET_PATH . 'views' );
	$wp_rocket->load();

	// Call defines and functions.
	require WP_ROCKET_FUNCTIONS_PATH . 'files.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'posts.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'admin.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'preload.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'cdn.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'varnish.php';
	require WP_ROCKET_DEPRECATED_PATH . 'deprecated.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.2.php';
	require WP_ROCKET_DEPRECATED_PATH . '3.3.php';
	require WP_ROCKET_3RD_PARTY_PATH . '3rd-party.php';
	require WP_ROCKET_COMMON_PATH . 'admin-bar.php';
	require WP_ROCKET_COMMON_PATH . 'emoji.php';
	require WP_ROCKET_COMMON_PATH . 'embeds.php';

	if ( rocket_valid_key() ) {
		require WP_ROCKET_COMMON_PATH . 'purge.php';
		require WP_ROCKET_COMMON_PATH . 'cron.php';

		if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
			require WP_ROCKET_FRONT_PATH . 'cdn.php';
		}

		if ( 0 < (int) get_rocket_option( 'do_cloudflare' ) ) {
			require WP_ROCKET_FUNCTIONS_PATH . 'cloudflare.php';
			require WP_ROCKET_VENDORS_PATH . 'ip_in_range.php';
			require WP_ROCKET_COMMON_PATH . 'cloudflare.php';
		}

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
	if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['rocket_nonce'] ), 'force_deactivation' ) ) {
		global $is_apache;
		$causes = array();

		// .htaccess problem.
		if ( $is_apache && ! rocket_direct_filesystem()->is_writable( get_home_path() . '.htaccess' ) ) {
			$causes[] = 'htaccess';
		}

		// wp-config problem.
		if ( ! rocket_direct_filesystem()->is_writable( rocket_find_wpconfig_path() ) ) {
			$causes[] = 'wpconfig';
		}

		if ( count( $causes ) ) {
			set_transient( $GLOBALS['current_user']->ID . '_donotdeactivaterocket', $causes );
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
		set_rocket_wp_cache_define( false );

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
}
register_deactivation_hook( WP_ROCKET_FILE, 'rocket_deactivation' );

/**
 * Tell WP what to do when plugin is activated.
 *
 * @since 1.1.0
 */
function rocket_activation() {
	// Last constants.
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
	define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

	if ( defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
		require WP_ROCKET_INC_PATH . 'domain-mapping.php';
	}

	require WP_ROCKET_FUNCTIONS_PATH . 'options.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'files.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
	require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php';
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php';
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php';

	if ( rocket_valid_key() ) {
		// Add All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess();

		// Add WP_CACHE constant in wp-config.php.
		set_rocket_wp_cache_define( true );
	}

	// Create the cache folders (wp-rocket & min).
	rocket_init_cache_dir();

	// Create the config folder (wp-rocket-config).
	rocket_init_config_dir();

	// Create advanced-cache.php file.
	rocket_generate_advanced_cache_file();

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
