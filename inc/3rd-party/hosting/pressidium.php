<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( defined( 'WP_NINUKIS_WP_NAME' ) ) {
	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 */
	function rocket_pressidium_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'Pressidium' );

		return $settings;
	}
	add_filter( 'rocket_varnish_field_settings', 'rocket_pressidium_varnish_field' );

	add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );
	// Prevent mandatory cookies on hosting with server cache.
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'rocket_display_nginx_addon', '__return_false' );

	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Pressidium Hosting
	 *
	 * @since 2.5.11
	 *
	 * @return void
	 */
	function rocket_clear_cache_after_pressidium() {
		if ( isset( $_POST['purge-all'] ) && current_user_can( 'manage_options' ) && check_admin_referer( WP_NINUKIS_WP_NAME . '-caching' ) ) {
			// Clear all caching files.
			rocket_clean_domain();

			// Preload cache.
			run_rocket_bot();
			run_rocket_sitemap_preload();
		}
	}
	add_action( 'admin_init', 'rocket_clear_cache_after_pressidium' );
}

if ( class_exists( 'Ninukis_Plugin' ) ) {
	/**
	 * Call the cache server to purge the cache with Pressidium hosting.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	function rocket_clean_pressidium() {
		$plugin = Ninukis_Plugin::get_instance();
		$plugin->purgeAllCaches();
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_pressidium' );
}
