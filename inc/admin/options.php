<?php
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || exit;

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
	if ( ! is_array( $oldvalue ) || ! is_array( $value ) ) {
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
		'analytics_enabled'           => true,
		'sucury_waf_cache_sync'       => true,
		'sucury_waf_api_key'          => true,
		'manual_preload'              => true,
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
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ( array_key_exists( 'minify_js', $oldvalue ) && array_key_exists( 'minify_js', $value ) && $oldvalue['minify_js'] !== $value['minify_js'] )
		||
		( array_key_exists( 'exclude_js', $oldvalue ) && array_key_exists( 'exclude_js', $value ) && $oldvalue['exclude_js'] !== $value['exclude_js'] )
		||
		( array_key_exists( 'cdn', $oldvalue ) && array_key_exists( 'cdn', $value ) && $oldvalue['cdn'] !== $value['cdn'] )
		||
		( array_key_exists( 'cdn_cnames', $oldvalue ) && array_key_exists( 'cdn_cnames', $value ) && $oldvalue['cdn_cnames'] !== $value['cdn_cnames'] )
	) {
		rocket_clean_minify( 'js' );
	}

	// Regenerate advanced-cache.php file.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST ) && ( ( isset( $oldvalue['do_caching_mobile_files'] ) && ! isset( $value['do_caching_mobile_files'] ) ) || ( ! isset( $oldvalue['do_caching_mobile_files'] ) && isset( $value['do_caching_mobile_files'] ) ) || ( isset( $oldvalue['do_caching_mobile_files'], $value['do_caching_mobile_files'] ) ) && $oldvalue['do_caching_mobile_files'] !== $value['do_caching_mobile_files'] ) ) {
		rocket_generate_advanced_cache_file();
	}

	// Update .htaccess file rules.
	flush_rocket_htaccess( ! rocket_valid_key() );

	// Update config file.
	rocket_generate_config_file();

	if ( isset( $oldvalue['analytics_enabled'], $value['analytics_enabled'] ) && $oldvalue['analytics_enabled'] !== $value['analytics_enabled'] && 1 === (int) $value['analytics_enabled'] ) {
		set_transient( 'rocket_analytics_optin', 1 );
	}
}
add_action( 'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), 'rocket_after_save_options', 10, 2 );

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
	$rocket_settings_errors = [];

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

		$rocket_settings_errors[] = [
			'setting' => 'general',
			'code'    => 'invalid_patterns',
			'message' => __( 'WP Rocket: ', 'rocket' ) . '</strong>' . $error_message . '<strong>',
			'type'    => 'error',
		];
	}

	// Clear WP Rocket database optimize cron if the setting has been modified.
	if ( ( isset( $newvalue['schedule_automatic_cleanup'], $oldvalue['schedule_automatic_cleanup'] ) && $newvalue['schedule_automatic_cleanup'] !== $oldvalue['schedule_automatic_cleanup'] ) || ( ( isset( $newvalue['automatic_cleanup_frequency'], $oldvalue['automatic_cleanup_frequency'] ) && $newvalue['automatic_cleanup_frequency'] !== $oldvalue['automatic_cleanup_frequency'] ) ) ) {
		if ( wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
			wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );
		}
	}

	// Regenerate the minify key if JS files have been modified.
	if ( ( isset( $newvalue['minify_js'], $oldvalue['minify_js'] ) && $newvalue['minify_js'] !== $oldvalue['minify_js'] )
		|| ( isset( $newvalue['exclude_js'], $oldvalue['exclude_js'] ) && $newvalue['exclude_js'] !== $oldvalue['exclude_js'] )
		|| ( isset( $oldvalue['cdn'] ) && ! isset( $newvalue['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $newvalue['cdn'] ) )
	) {
		$newvalue['minify_js_key'] = create_rocket_uniqid();
	}

	// Checked the SSL option if the whole website is on SSL.
	if ( rocket_is_ssl_website() ) {
		$newvalue['cache_ssl'] = 1;
	}

	if ( ! rocket_get_constant( 'WP_ROCKET_ADVANCED_CACHE' ) ) {
		rocket_generate_advanced_cache_file();
	}

	$keys = get_transient( WP_ROCKET_SLUG );

	if ( $keys ) {
		delete_transient( WP_ROCKET_SLUG );
		$newvalue = array_merge( $newvalue, $keys );
	}

	if ( ! $rocket_settings_errors ) {
		return $newvalue;
	}

	/**
	 * Display an error notice.
	 * The notices are stored directly in the transient instead of using `add_settings_error()`, to make sure they are displayed even if we’re outside an admin screen.
	 */
	$transient_errors = get_transient( 'settings_errors' );

	if ( ! $transient_errors || ! is_array( $transient_errors ) ) {
		$transient_errors = [];
	}

	$transient_errors = array_merge( $transient_errors, $rocket_settings_errors );

	set_transient( 'settings_errors', $transient_errors, 30 );

	return $newvalue;
}
add_filter( 'pre_update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), 'rocket_pre_main_option', 10, 2 );

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
