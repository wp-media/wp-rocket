<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'WpeCommon' ) && function_exists( 'wpe_param' ) ) {
	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 */
	function rocket_wpengine_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'WP Engine' );

		return $settings;
	}
	add_filter( 'rocket_varnish_field_settings', 'rocket_wpengine_varnish_field' );

	add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );
	// Prevent mandatory cookies on hosting with server cache.
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'rocket_advanced_cache_file', '__return_empty_string' );
	add_action( 'admin_init', function() {
		remove_action( 'admin_notices', 'rocket_warning_advanced_cache_permissions' );
		remove_action( 'admin_notices', 'rocket_warning_advanced_cache_not_ours' );
	});

	/**
	 * Always keep WP_CACHE constant to true
	 *
	 * @since 2.8.6
	 */
	add_filter( 'set_rocket_wp_cache_define', '__return_true' );

	/**
	 * Conflict with WP Engine caching system
	 *
	 * @since 2.6.4
	 */
	function rocket_stop_generate_caching_files_on_wpengine() {
		add_filter( 'do_rocket_generate_caching_files', '__return_false' );
	}
	add_action( 'init', 'rocket_stop_generate_caching_files_on_wpengine' );

	/**
	 * Run WP Rocket preload bot after purged the Varnish cache via WP Engine Hosting
	 *
	 * @since 2.6.4
	 *
	 * @return void
	 */
	function rocket_run_rocket_bot_after_wpengine() {
		if ( wpe_param( 'purge-all' ) && defined( 'PWP_NAME' ) && check_admin_referer( PWP_NAME . '-config' ) ) {
			// Preload cache.
			run_rocket_bot();
			run_rocket_sitemap_preload();
		}
	}
	add_action( 'admin_init', 'rocket_run_rocket_bot_after_wpengine' );

	/**
	 * Call the cache server to purge the cache with WP Engine hosting.
	 *
	 * @since 2.6.4
	 */
	function rocket_clean_wpengine() {
		if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
			WpeCommon::purge_memcached();
		}

		if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
			WpeCommon::purge_varnish_cache();
		}
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_wpengine' );

	/**
	 * Gets WP Engine CDN Domain
	 *
	 * @since 2.8.6
	 * @author Jonathan Buttigieg
	 *
	 * return string $cdn_domain the WP Engine CDN Domain
	 */
	function rocket_get_wp_engine_cdn_domain() {
		global $wpe_netdna_domains, $wpe_netdna_domains_secure;

		$cdn_domain = '';
		$is_ssl     = @$_SERVER['HTTPS'];

		if ( preg_match( '/^[oO][fF]{2}$/', $is_ssl ) ) {
			$is_ssl = false;  // have seen this!
		}

		$native_schema = $is_ssl ? 'https' : 'http';

		$domains = $wpe_netdna_domains;
		// Determine the CDN, if any.
		if ( $is_ssl ) {
			$domains = $wpe_netdna_domains_secure;
		}

			$wpengine   = WpeCommon::instance();
			$cdn_domain = $wpengine->get_cdn_domain( $domains, home_url(), $is_ssl );

		if ( ! empty( $cdn_domain ) ) {
			$cdn_domain = $native_schema . '://' . $cdn_domain;
		}

		return $cdn_domain;
	}

	/**
	 * Add WP Rocket footprint on Buffer
	 *
	 * @since 3.3.2
	 * @author Remy Perona
	 *
	 * @param string $buffer HTML content
	 * @return string
	 */
	function rocket_wpengine_add_footprint( $buffer ) {
		if (! preg_match( '/<\/html>/i', $buffer ) ) {
			return $buffer;
		}

		$footprint = defined( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' ) ?
						"\n" . '<!-- Optimized for great performance' :
						"\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . WP_ROCKET_PLUGIN_NAME . '. Learn more: https://wp-rocket.me';
		$footprint .= ' -->';

		return $buffer . $footprint;
	}
	add_filter( 'rocket_buffer', 'rocket_wpengine_add_footprint', 50 );
}
