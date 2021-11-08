<?php
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Tell WP what to do when admin is loaded aka upgrader
 *
 * @since 1.0
 */
function rocket_upgrader() {
	// Grab some infos.
	$actual_version = get_rocket_option( 'version' );
	// You can hook the upgrader to trigger any action when WP Rocket is upgraded.
	// first install.
	if ( ! $actual_version ) {
		do_action( 'wp_rocket_first_install' );
	}
	// already installed but got updated.
	elseif ( WP_ROCKET_VERSION !== $actual_version ) {
		do_action( 'wp_rocket_upgrade', WP_ROCKET_VERSION, $actual_version );
	}

	// If any upgrade has been done, we flush and update version number.
	if ( did_action( 'wp_rocket_first_install' ) || did_action( 'wp_rocket_upgrade' ) ) {
		flush_rocket_htaccess();

		rocket_renew_all_boxes( 0, [ 'rocket_warning_plugin_modification' ] );

		$options            = get_option( WP_ROCKET_SLUG ); // do not use get_rocket_option() here.
		$options['version'] = WP_ROCKET_VERSION;

		$keys = rocket_check_key();
		if ( is_array( $keys ) ) {
			$options = array_merge( $keys, $options );
		}

		update_option( WP_ROCKET_SLUG, $options );
	}

	$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

	if (
		'wprocket' === $page
		&&
		did_action( 'wp_rocket_upgrade' )
	) {
		wp_safe_redirect( esc_url_raw( admin_url( 'options-general.php?page=wprocket' ) ) );
		exit;
	}

	if ( ! rocket_valid_key() && current_user_can( 'rocket_manage_options' ) && 'wprocket' === $page ) {
		add_action( 'admin_notices', 'rocket_need_api_key' );
	}
}
add_action( 'admin_init', 'rocket_upgrader' );

/**
 * Maybe reset opcache after WP Rocket update.
 *
 * @since  3.1
 * @author Grégory Viguier
 *
 * @param object $wp_upgrader Plugin_Upgrader instance.
 * @param array  $hook_extra  {
 *     Array of bulk item update data.
 *
 *     @type string $action  Type of action. Default 'update'.
 *     @type string $type    Type of update process. Accepts 'plugin', 'theme', 'translation', or 'core'.
 *     @type bool   $bulk    Whether the update process is a bulk update. Default true.
 *     @type array  $plugins Array of the basename paths of the plugins' main files.
 * }
 */
function rocket_maybe_reset_opcache( $wp_upgrader, $hook_extra ) {
	static $rocket_path;

	if ( ! isset( $hook_extra['action'], $hook_extra['type'], $hook_extra['plugins'] ) ) {
		return;
	}

	if ( 'update' !== $hook_extra['action'] || 'plugin' !== $hook_extra['type'] || ! is_array( $hook_extra['plugins'] ) ) {
		return;
	}

	$plugins = array_flip( $hook_extra['plugins'] );

	if ( ! isset( $rocket_path ) ) {
		$rocket_path = plugin_basename( WP_ROCKET_FILE );
	}

	if ( ! isset( $plugins[ $rocket_path ] ) ) {
		return;
	}

	rocket_reset_opcache();
}
add_action( 'upgrader_process_complete', 'rocket_maybe_reset_opcache', 20, 2 );

/**
 * Reset PHP opcache.
 *
 * @since  3.1
 * @author Grégory Viguier
 */
function rocket_reset_opcache() {
	static $can_reset;

	/**
	 * Triggers before WP Rocket tries to reset OPCache
	 *
	 * @since 3.2.5
	 * @author Remy Perona
	 */
	do_action( 'rocket_before_reset_opcache' );

	if ( ! isset( $can_reset ) ) {
		if ( ! function_exists( 'opcache_reset' ) ) {
			$can_reset = false;

			return false;
		}

		$restrict_api = ini_get( 'opcache.restrict_api' );

		if ( $restrict_api && strpos( __FILE__, $restrict_api ) !== 0 ) {
			$can_reset = false;

			return false;
		}

		$can_reset = true;
	}

	if ( ! $can_reset ) {
		return false;
	}

	$opcache_reset = opcache_reset();

	/**
	 * Triggers after WP Rocket tries to reset OPCache
	 *
	 * @since 3.2.5
	 * @author Remy Perona
	 */
	do_action( 'rocket_after_reset_opcache' );

	return $opcache_reset;
}

/**
 * Keeps this function up to date at each version
 *
 * @since 1.0
 */
function rocket_first_install() {
	// Generate an random key for cache dir of user.
	$secret_cache_key = create_rocket_uniqid();

	// Generate an random key for minify md5 filename.
	$minify_css_key = create_rocket_uniqid();
	$minify_js_key  = create_rocket_uniqid();

	// Create Option.
	add_option(
		rocket_get_constant( 'WP_ROCKET_SLUG' ),
		/**
		 * Filters the default rocket options array
		 *
		 * @since 2.8
		 *
		 * @param array Array of default rocket options
		 */
		apply_filters(
			'rocket_first_install_options',
			[
				'secret_cache_key'            => $secret_cache_key,
				'cache_mobile'                => 1,
				'do_caching_mobile_files'     => 0,
				'cache_webp'                  => 0,
				'cache_logged_user'           => 0,
				'cache_ssl'                   => 1,
				'emoji'                       => 1,
				'cache_reject_uri'            => [],
				'cache_reject_cookies'        => [],
				'cache_reject_ua'             => [],
				'cache_query_strings'         => [],
				'cache_purge_pages'           => [],
				'purge_cron_interval'         => 10,
				'purge_cron_unit'             => 'HOUR_IN_SECONDS',
				'exclude_css'                 => [],
				'exclude_js'                  => [],
				'exclude_inline_js'           => [],
				'defer_all_js'                => 0,
				'async_css'                   => 0,
				'critical_css'                => '',
				'lazyload'                    => 0,
				'lazyload_iframes'            => 0,
				'lazyload_youtube'            => 0,
				'minify_css'                  => 0,
				'minify_css_key'              => $minify_css_key,
				'minify_concatenate_css'      => 0,
				'minify_js'                   => 0,
				'minify_js_key'               => $minify_js_key,
				'minify_concatenate_js'       => 0,
				'minify_google_fonts'         => 1,
				'manual_preload'              => 1,
				'sitemap_preload'             => 0,
				'sitemap_preload_url_crawl'   => '500000',
				'sitemaps'                    => [],
				'dns_prefetch'                => 0,
				'preload_fonts'               => [],
				'database_revisions'          => 0,
				'database_auto_drafts'        => 0,
				'database_trashed_posts'      => 0,
				'database_spam_comments'      => 0,
				'database_trashed_comments'   => 0,
				'database_all_transients'     => 0,
				'database_optimize_tables'    => 0,
				'schedule_automatic_cleanup'  => 0,
				'automatic_cleanup_frequency' => 'daily',
				'cdn'                         => 0,
				'cdn_cnames'                  => [],
				'cdn_zone'                    => [],
				'cdn_reject_files'            => [],
				'do_cloudflare'               => 0,
				'cloudflare_email'            => '',
				'cloudflare_api_key'          => '',
				'cloudflare_zone_id'          => '',
				'cloudflare_devmode'          => 0,
				'cloudflare_protocol_rewrite' => 0,
				'cloudflare_auto_settings'    => 0,
				'cloudflare_old_settings'     => '',
				'control_heartbeat'           => 1,
				'heartbeat_site_behavior'     => 'reduce_periodicity',
				'heartbeat_admin_behavior'    => 'reduce_periodicity',
				'heartbeat_editor_behavior'   => 'reduce_periodicity',
				'varnish_auto_purge'          => 0,
				'analytics_enabled'           => 0,
				'sucury_waf_cache_sync'       => 0,
				'sucury_waf_api_key'          => '',
			]
		)
	);
	rocket_dismiss_box( 'rocket_warning_plugin_modification' );
}
add_action( 'wp_rocket_first_install', 'rocket_first_install' );

/**
 * What to do when Rocket is updated, depending on versions
 *
 * @since 1.0
 *
 * @param string $wp_rocket_version Latest WP Rocket version.
 * @param string $actual_version Installed WP Rocket version.
 */
function rocket_new_upgrade( $wp_rocket_version, $actual_version ) {
	if ( version_compare( $actual_version, '2.4.1', '<' ) ) {
		delete_transient( 'rocket_ask_for_update' );
	}

	if ( version_compare( $actual_version, '2.8', '<' ) ) {
		$options                              = get_option( WP_ROCKET_SLUG );
		$options['manual_preload']            = 1;
		$options['automatic_preload']         = 1;
		$options['sitemap_preload_url_crawl'] = '500000';

		update_option( WP_ROCKET_SLUG, $options );
	}

	// Deactivate CloudFlare completely if PHP Version is lower than 5.4.
	if ( version_compare( $actual_version, '2.8.16', '<' ) ) {
		$options                                = get_option( WP_ROCKET_SLUG );
		$options['do_cloudflare']               = 0;
		$options['cloudflare_email']            = '';
		$options['cloudflare_api_key']          = '';
		$options['cloudflare_devmode']          = 0;
		$options['cloudflare_protocol_rewrite'] = 0;
		$options['cloudflare_auto_settings']    = 0;
		$options['cloudflare_old_settings']     = '';

		update_option( WP_ROCKET_SLUG, $options );
	}

	// Disable minification options if they're active in Autoptimize.
	if ( version_compare( $actual_version, '2.9.5', '<' ) ) {
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
			if ( 'on' === get_option( 'autoptimize_css' ) ) {
				update_rocket_option( 'minify_css', 0 );
			}

			if ( 'on' === get_option( 'autoptimize_js' ) ) {
				update_rocket_option( 'minify_js', 0 );
			}
		}
	}

	// Delete old transients.
	if ( version_compare( $actual_version, '2.9.7', '<' ) ) {
		delete_transient( 'rocket_check_licence_30' );
		delete_transient( 'rocket_check_licence_1' );
	}

	if ( version_compare( $actual_version, '2.11', '<' ) ) {
		rocket_clean_minify();
	}

	if ( version_compare( $actual_version, '3.2', '<' ) ) {
		// Default Heartbeat settings.
		$options                              = get_option( WP_ROCKET_SLUG, [] );
		$options['heartbeat_site_behavior']   = 'reduce_periodicity';
		$options['heartbeat_admin_behavior']  = 'reduce_periodicity';
		$options['heartbeat_editor_behavior'] = 'reduce_periodicity';

		if ( ! empty( $options['automatic_preload'] ) || ! empty( $options['sitemap_preload'] ) ) {
			$options['manual_preload'] = 1;
		}

		update_option( WP_ROCKET_SLUG, $options );
		rocket_generate_config_file();

		// Create a .htaccess file in the log folder.
		$handler = Logger::get_stream_handler();

		if ( method_exists( $handler, 'create_htaccess_file' ) ) {
			try {
				$success = $handler->create_htaccess_file();
			} catch ( \Exception $e ) {
				$success = false;
			}

			if ( ! $success ) {
				Logger::delete_log_file();
			}
		}
	}

	if ( version_compare( $actual_version, '3.2.0.1', '<' ) ) {
		wp_safe_remote_get( esc_url( home_url() ) );
	}

	if ( version_compare( $actual_version, '3.3.6', '<' ) ) {
		delete_site_transient( 'update_wprocket' );
		delete_site_transient( 'update_wprocket_response' );

		if ( get_rocket_option( 'do_cloudflare' ) && get_rocket_option( 'cloudflare_auto_settings' ) ) {
			if ( function_exists( 'set_rocket_cloudflare_browser_cache_ttl' ) ) {
				set_rocket_cloudflare_browser_cache_ttl( '31536000' );
			}
		}
	}

	if ( rocket_is_ssl_website() ) {
		if ( 1 !== (int) get_rocket_option( 'cache_ssl' ) ) {
			update_rocket_option( 'cache_ssl', 1 );
			rocket_generate_config_file();
		}
	}

	if ( version_compare( $actual_version, '3.4', '<' ) ) {
		wp_clear_scheduled_hook( 'rocket_purge_time_event' );
	}

	if ( version_compare( $actual_version, '3.6', '<' ) ) {
		rocket_clean_cache_busting();
		rocket_clean_domain();
	}

	if ( version_compare( $actual_version, '3.6.1', '<' ) ) {
		rocket_generate_config_file();
	}

	if ( version_compare( $actual_version, '3.7', '<' ) ) {
		rocket_clean_minify( 'css' );
		rocket_generate_advanced_cache_file();
	}

	if ( version_compare( $actual_version, '3.8.1', '<' ) ) {
		$options = get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) );
		unset( $options['dequeue_jquery_migrate'] );
		update_option( rocket_get_constant( 'WP_ROCKET_SLUG' ), $options );
	}

	if ( version_compare( $actual_version, '3.9', '<' ) ) {
		$busting_path = rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH' );

		rocket_rrmdir( $busting_path . 'facebook-tracking' );
		rocket_rrmdir( $busting_path . 'google-tracking' );
		wp_clear_scheduled_hook( 'rocket_facebook_tracking_cache_update' );
		wp_clear_scheduled_hook( 'rocket_google_tracking_cache_update' );
	}

	if ( version_compare( $actual_version, '3.10', '<' ) ) {
		$options = get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) );
		if (
			isset( $options['async_css'] ) && $options['async_css'] &&
			isset( $options['remove_unused_css'] ) && $options['remove_unused_css']
		) {
			$options['async_css'] = 0;
			$cache_path           = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH' );
			rocket_rrmdir( $cache_path . 'used-css' );
			update_option( rocket_get_constant( 'WP_ROCKET_SLUG' ), $options );
		}
	}
}
add_action( 'wp_rocket_upgrade', 'rocket_new_upgrade', 10, 2 );
