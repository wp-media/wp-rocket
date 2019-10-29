<?php
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * When our settings are saved: purge, flush, preload!
 *
 * @since 1.0
 *
 * When the settins menu is hidden, redirect on the main settings page to avoid the same thing
 * (Only when a form is sent from our options page )
 *
 * @since 2.1
 *
 * @param array $oldvalue An array of previous values for the settings.
 * @param array $value An array of submitted values for the settings.
 */
function rocket_after_save_options( $oldvalue, $value ) {
	if ( ! ( is_array( $oldvalue ) && is_array( $value ) ) ) {
		return;
	}

	// These values do not need to clean the cache domain.
	$removed = [
		'cache_mobile'                => true,
		'purge_cron_interval'         => true,
		'purge_cron_unit'             => true,
		'sitemap_preload'             => true,
		'sitemaps'                    => true,
		'database_revisions'          => true,
		'database_auto_drafts'        => true,
		'database_trashed_posts'      => true,
		'database_spam_comments'      => true,
		'database_trashed_comments'   => true,
		'database_expired_transients' => true,
		'database_all_transients'     => true,
		'database_optimize_tables'    => true,
		'schedule_automatic_cleanup'  => true,
		'automatic_cleanup_frequency' => true,
		'do_cloudflare'               => true,
		'cloudflare_email'            => true,
		'cloudflare_api_key'          => true,
		'cloudflare_zone_id'          => true,
		'cloudflare_devmode'          => true,
		'cloudflare_auto_settings'    => true,
		'cloudflare_old_settings'     => true,
		'heartbeat_admin_behavior'    => true,
		'heartbeat_editor_behavior'   => true,
		'varnish_auto_purge'          => true,
		'do_beta'                     => true,
		'analytics_enabled'           => true,
		'sucury_waf_cache_sync'       => true,
		'sucury_waf_api_key'          => true,
	];

	// Create 2 arrays to compare.
	$oldvalue_diff = array_diff_key( $oldvalue, $removed );
	$value_diff    = array_diff_key( $value, $removed );

	// If it's different, clean the domain.
	if ( md5( wp_json_encode( $oldvalue_diff ) ) !== md5( wp_json_encode( $value_diff ) ) ) {
		// Purge all cache files.
		rocket_clean_domain();

		wp_remote_get(
			home_url(),
			[
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => 'WP Rocket/Homepage Preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ),
			]
		);
	}

	// Purge all minify cache files.
	if ( ! empty( $_POST ) && ( $oldvalue['minify_css'] !== $value['minify_css'] || $oldvalue['exclude_css'] !== $value['exclude_css'] ) || ( isset( $oldvalue['cdn'] ) && ! isset( $value['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $value['cdn'] ) ) ) {
		rocket_clean_minify( 'css' );
	}

	if ( ! empty( $_POST ) && ( $oldvalue['minify_js'] !== $value['minify_js'] || $oldvalue['exclude_js'] !== $value['exclude_js'] ) || ( isset( $oldvalue['cdn'] ) && ! isset( $value['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $value['cdn'] ) ) ) {
		rocket_clean_minify( 'js' );
	}

	// Purge all cache busting files.
	if ( ! empty( $_POST ) && ( $oldvalue['remove_query_strings'] !== $value['remove_query_strings'] ) ) {
		rocket_clean_cache_busting();
	}

	if ( ! empty( $_POST ) &&
			( ( isset( $oldvalue['cloudflare_email'], $value['cloudflare_email'] ) && $oldvalue['cloudflare_email'] !== $value['cloudflare_email'] ) ||
			( isset( $oldvalue['cloudflare_api_key'], $value['cloudflare_api_key'] ) && $oldvalue['cloudflare_api_key'] !== $value['cloudflare_api_key'] ) ||
			( isset( $oldvalue['cloudflare_zone_id'], $value['cloudflare_zone_id'] ) && $oldvalue['cloudflare_zone_id'] !== $value['cloudflare_zone_id'] ) )
			) {
		// Check Cloudflare input data and display error message.
		if ( get_rocket_option( 'do_cloudflare' ) && function_exists( 'rocket_is_api_keys_valid_cloudflare' ) ) {
			$is_api_keys_valid_cloudflare = rocket_is_api_keys_valid_cloudflare( $value['cloudflare_email'], $value['cloudflare_api_key'], $value['cloudflare_zone_id'], false );
			if ( is_wp_error( $is_api_keys_valid_cloudflare ) ) {
				$cloudflare_error_message = $is_api_keys_valid_cloudflare->get_error_message();
				add_settings_error( 'general', 'cloudflare_api_key_invalid', __( 'WP Rocket: ', 'rocket' ) . '</strong>' . $cloudflare_error_message . '<strong>', 'error' );
			}
		}
	}

	// Update CloudFlare Development Mode.
	$cloudflare_update_result = array();

	if ( ! empty( $_POST ) && isset( $oldvalue['cloudflare_devmode'], $value['cloudflare_devmode'] ) && (int) $oldvalue['cloudflare_devmode'] !== (int) $value['cloudflare_devmode'] ) {
		$cloudflare_dev_mode_return = set_rocket_cloudflare_devmode( $value['cloudflare_devmode'] );

		if ( is_wp_error( $cloudflare_dev_mode_return ) ) {
			$cloudflare_update_result[] = array(
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare development mode error: %s', 'rocket' ), $cloudflare_dev_mode_return->get_error_message() ),
			);
		} else {
			$cloudflare_update_result[] = array(
				'result'  => 'success',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare development mode %s', 'rocket' ), $cloudflare_dev_mode_return ),
			);
		}
	}

	// Update CloudFlare settings.
	if ( ! empty( $_POST ) && ! empty( $value['do_cloudflare'] ) && isset( $oldvalue['cloudflare_auto_settings'], $value['cloudflare_auto_settings'] ) && (int) $oldvalue['cloudflare_auto_settings'] !== (int) $value['cloudflare_auto_settings'] ) {
		$cf_old_settings = explode( ',', $value['cloudflare_old_settings'] );

		// Set Cache Level to Aggressive.
		$cf_cache_level        = ( isset( $cf_old_settings[0] ) && 0 === $value['cloudflare_auto_settings'] ) ? $cf_old_settings[0] : 'aggressive';
		$cf_cache_level_return = set_rocket_cloudflare_cache_level( $cf_cache_level );

		if ( is_wp_error( $cf_cache_level_return ) ) {
			$cloudflare_update_result[] = array(
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare cache level error: %s', 'rocket' ), $cf_cache_level_return->get_error_message() ),
			);
		} else {
			if ( 'aggressive' === $cf_cache_level_return ) {
				$cf_cache_level_return = _x( 'Standard', 'Cloudflare caching level', 'rocket' );
			}

			$cloudflare_update_result[] = array(
				'result'  => 'success',
				// translators: %s is the caching level returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare cache level set to %s', 'rocket' ), $cf_cache_level_return ),
			);
		}

		// Active Minification for HTML, CSS & JS.
		$cf_minify        = ( isset( $cf_old_settings[1] ) && 0 === $value['cloudflare_auto_settings'] ) ? $cf_old_settings[1] : 'on';
		$cf_minify_return = set_rocket_cloudflare_minify( $cf_minify );

		if ( is_wp_error( $cf_minify_return ) ) {
			$cloudflare_update_result[] = array(
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare minification error: %s', 'rocket' ), $cf_minify_return->get_error_message() ),
			);
		} else {
			$cloudflare_update_result[] = array(
				'result'  => 'success',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare minification %s', 'rocket' ), $cf_minify_return ),
			);
		}

		// Deactivate Rocket Loader to prevent conflicts.
		$cf_rocket_loader        = ( isset( $cf_old_settings[2] ) && 0 === $value['cloudflare_auto_settings'] ) ? $cf_old_settings[2] : 'off';
		$cf_rocket_loader_return = set_rocket_cloudflare_rocket_loader( $cf_rocket_loader );

		if ( is_wp_error( $cf_rocket_loader_return ) ) {
			$cloudflare_update_result[] = array(
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare rocket loader error: %s', 'rocket' ), $cf_rocket_loader_return->get_error_message() ),
			);
		} else {
			$cloudflare_update_result[] = array(
				'result'  => 'success',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare rocket loader %s', 'rocket' ), $cf_rocket_loader_return ),
			);
		}

		// Set Browser cache to 1 year.
		$cf_browser_cache_ttl    = ( isset( $cf_old_settings[3] ) && 0 === $value['cloudflare_auto_settings'] ) ? $cf_old_settings[3] : '31536000';
		$cf_browser_cache_return = set_rocket_cloudflare_browser_cache_ttl( $cf_browser_cache_ttl );

		if ( is_wp_error( $cf_browser_cache_return ) ) {
			$cloudflare_update_result[] = array(
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare browser cache error: %s', 'rocket' ), $cf_browser_cache_return->get_error_message() ),
			);
		} else {
			$cloudflare_update_result[] = array(
				'result'  => 'success',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare browser cache set to %s seconds', 'rocket' ), $cf_browser_cache_return ),
			);
		}
	}

	if ( (bool) $cloudflare_update_result ) {
		set_transient( $GLOBALS['current_user']->ID . '_cloudflare_update_settings', $cloudflare_update_result );
	}

	// Regenerate advanced-cache.php file.
	if ( ! empty( $_POST ) && ( ( isset( $oldvalue['do_caching_mobile_files'] ) && ! isset( $value['do_caching_mobile_files'] ) ) || ( ! isset( $oldvalue['do_caching_mobile_files'] ) && isset( $value['do_caching_mobile_files'] ) ) || ( isset( $oldvalue['do_caching_mobile_files'], $value['do_caching_mobile_files'] ) ) && $oldvalue['do_caching_mobile_files'] !== $value['do_caching_mobile_files'] ) ) {
		rocket_generate_advanced_cache_file();
	}

	// Update .htaccess file rules.
	flush_rocket_htaccess( ! rocket_valid_key() );

	// Update config file.
	rocket_generate_config_file();

	// Set WP_CACHE constant in wp-config.php.
	if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}

	if ( isset( $oldvalue['analytics_enabled'], $value['analytics_enabled'] ) && $oldvalue['analytics_enabled'] !== $value['analytics_enabled'] && 1 === (int) $value['analytics_enabled'] ) {
		set_transient( 'rocket_analytics_optin', 1 );
	}
}
add_action( 'update_option_' . WP_ROCKET_SLUG, 'rocket_after_save_options', 10, 2 );

/**
 * Perform actions when settings are saved.
 *
 * @since 1.0
 *
 * @param array $newvalue An array of submitted options values.
 * @param array $oldvalue An array of previous options values.
 * @return array Updated submitted options values.
 */
function rocket_pre_main_option( $newvalue, $oldvalue ) {
	// Make sure that fields that allow users to enter patterns are well formatted.
	$is_form_submit = filter_input( INPUT_POST, 'option_page', FILTER_SANITIZE_STRING );
	$is_form_submit = WP_ROCKET_PLUGIN_SLUG === $is_form_submit;
	$errors         = [];
	$pattern_labels = [
		'exclude_css'       => __( 'Excluded CSS Files', 'rocket' ),
		'exclude_inline_js' => __( 'Excluded Inline JavaScript', 'rocket' ),
		'exclude_js'        => __( 'Excluded JavaScript Files', 'rocket' ),
		'cache_reject_uri'  => __( 'Never Cache URL(s)', 'rocket' ),
		'cache_reject_ua'   => __( 'Never Cache User Agent(s)', 'rocket' ),
		'cache_purge_pages' => __( 'Always Purge URL(s)', 'rocket' ),
		'cdn_reject_files'  => __( 'Exclude files from CDN', 'rocket' ),
	];

	foreach ( $pattern_labels as $pattern_field => $label ) {
		if ( empty( $newvalue[ $pattern_field ] ) ) {
			// The field is empty.
			continue;
		}

		// Sanitize.
		$newvalue[ $pattern_field ] = rocket_sanitize_textarea_field( $pattern_field, $newvalue[ $pattern_field ] );

		// Validate.
		$newvalue[ $pattern_field ] = array_filter(
			$newvalue[ $pattern_field ],
			function( $excluded ) use ( $pattern_field, $label, $is_form_submit, &$errors ) {
				if ( false === @preg_match( '#' . str_replace( '#', '\#', $excluded ) . '#', 'dummy-sample' ) && $is_form_submit ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					/* translators: 1 and 2 can be anything. */
					$errors[ $pattern_field ] = sprintf( __( '%1$s: <em>%2$s</em>.', 'rocket' ), $label, esc_html( $excluded ) );
					return false;
				}

				return true;
			}
		);
	}

	if ( $errors ) {
		$error_message  = _n( 'The following pattern is invalid and has been removed:', 'The following patterns are invalid and have been removed:', count( $errors ), 'rocket' );
		$error_message .= '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>';
		$errors         = [];

		add_settings_error( 'general', 'invalid_patterns', $error_message, 'error' );
	}

	// Make sure that fields that allow users to enter patterns are well formatted.
	$is_form_submit = filter_input( INPUT_POST, 'option_page', FILTER_SANITIZE_STRING );
	$is_form_submit = WP_ROCKET_PLUGIN_SLUG === $is_form_submit;
	$errors         = [];
	$pattern_labels = [
		'exclude_css'       => __( 'Excluded CSS Files', 'rocket' ),
		'exclude_inline_js' => __( 'Excluded Inline JavaScript', 'rocket' ),
		'exclude_js'        => __( 'Excluded JavaScript Files', 'rocket' ),
		'cache_reject_uri'  => __( 'Never Cache URL(s)', 'rocket' ),
		'cache_reject_ua'   => __( 'Never Cache User Agent(s)', 'rocket' ),
		'cache_purge_pages' => __( 'Always Purge URL(s)', 'rocket' ),
		'cdn_reject_files'  => __( 'Exclude files from CDN', 'rocket' ),
	];

	foreach ( $pattern_labels as $pattern_field => $label ) {
		if ( empty( $newvalue[ $pattern_field ] ) ) {
			// The field is empty.
			continue;
		}

		// Sanitize.
		$newvalue[ $pattern_field ] = rocket_sanitize_textarea_field( $pattern_field, $newvalue[ $pattern_field ] );

		// Validate.
		$newvalue[ $pattern_field ] = array_filter(
			$newvalue[ $pattern_field ],
			function( $excluded ) use ( $pattern_field, $label, $is_form_submit, &$errors ) {
				if ( false === @preg_match( '#' . str_replace( '#', '\#', $excluded ) . '#', 'dummy-sample' ) && $is_form_submit ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					/* translators: 1 and 2 can be anything. */
					$errors[ $pattern_field ] = sprintf( __( '%1$s: <em>%2$s</em>.', 'rocket' ), $label, esc_html( $excluded ) );
					return false;
				}

				return true;
			}
		);
	}

	if ( $errors ) {
		$error_message  = _n( 'The following pattern is invalid and has been removed:', 'The following patterns are invalid and have been removed:', count( $errors ), 'rocket' );
		$error_message .= '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>';
		$errors         = [];

		add_settings_error( 'general', 'invalid_patterns', $error_message, 'error' );
	}

	// Clear WP Rocket database optimize cron if the setting has been modified.
	if ( ( isset( $newvalue['schedule_automatic_cleanup'], $oldvalue['schedule_automatic_cleanup'] ) && $newvalue['schedule_automatic_cleanup'] !== $oldvalue['schedule_automatic_cleanup'] ) || ( ( isset( $newvalue['automatic_cleanup_frequency'], $oldvalue['automatic_cleanup_frequency'] ) && $newvalue['automatic_cleanup_frequency'] !== $oldvalue['automatic_cleanup_frequency'] ) ) ) {
		if ( wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
			wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );
		}
	}

	// Regenerate the minify key if CSS files have been modified.
	if ( ( isset( $newvalue['minify_css'], $oldvalue['minify_css'] ) && $newvalue['minify_css'] !== $oldvalue['minify_css'] )
		|| ( isset( $newvalue['exclude_css'], $oldvalue['exclude_css'] ) && $newvalue['exclude_css'] !== $oldvalue['exclude_css'] )
		|| ( isset( $oldvalue['cdn'] ) && ! isset( $newvalue['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $newvalue['cdn'] ) )
	) {
		$newvalue['minify_css_key'] = create_rocket_uniqid();
	}

	// Regenerate the minify key if JS files have been modified.
	if ( ( isset( $newvalue['minify_js'], $oldvalue['minify_js'] ) && $newvalue['minify_js'] !== $oldvalue['minify_js'] )
		|| ( isset( $newvalue['exclude_js'], $oldvalue['exclude_js'] ) && $newvalue['exclude_js'] !== $oldvalue['exclude_js'] )
		|| ( isset( $oldvalue['cdn'] ) && ! isset( $newvalue['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $newvalue['cdn'] ) )
	) {
		$newvalue['minify_js_key'] = create_rocket_uniqid();
	}

	// Save old CloudFlare settings.
	if ( ( isset( $newvalue['cloudflare_auto_settings'], $oldvalue['cloudflare_auto_settings'] ) && $newvalue['cloudflare_auto_settings'] !== $oldvalue['cloudflare_auto_settings'] && 1 === $newvalue['cloudflare_auto_settings'] ) && 0 < (int) get_rocket_option( 'do_cloudflare' ) ) {
		$cf_settings                         = get_rocket_cloudflare_settings();
		$newvalue['cloudflare_old_settings'] = ( ! is_wp_error( $cf_settings ) ) ? implode( ',', array_filter( $cf_settings ) ) : '';
	}

	// Checked the SSL option if the whole website is on SSL.
	if ( rocket_is_ssl_website() ) {
		$newvalue['cache_ssl'] = 1;
	}

	if ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) ) {
		rocket_generate_advanced_cache_file();
	}

	$keys = get_transient( WP_ROCKET_SLUG );
	if ( $keys ) {
		delete_transient( WP_ROCKET_SLUG );
		$newvalue = array_merge( $newvalue, $keys );
	}

	if ( ! function_exists( 'get_settings_errors' ) ) {
		require_once ABSPATH . 'wp-admin/includes/template.php';
	}

	if ( get_settings_errors() ) {
		// Display an error notice.
		set_transient( 'settings_errors', get_settings_errors(), 30 );
	}

	return $newvalue;
}
add_filter( 'pre_update_option_' . WP_ROCKET_SLUG, 'rocket_pre_main_option', 10, 2 );

/**
 * Auto-activate the SSL cache if the website URL is updated with https protocol
 *
 * @since 2.7
 *
 * @param array $old_value An array of previous options values.
 * @param array $value     An array of submitted options values.
 */
function rocket_update_ssl_option_after_save_home_url( $old_value, $value ) {
	if ( $old_value === $value || get_rocket_option( 'cache_ssl' ) ) {
		return;
	}

	$scheme = rocket_extract_url_component( $value, PHP_URL_SCHEME );

	update_rocket_option( 'cache_ssl', 'https' === $scheme ? 1 : 0 );
}
add_action( 'update_option_home', 'rocket_update_ssl_option_after_save_home_url', 10, 2 );
