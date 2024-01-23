<?php

defined( 'ABSPATH' ) || exit;

/**
 * Returns the current version of the SG Optimizer plugin.
 *
 * @since  3.2.3.1
 * @author Remy Perona
 *
 * @return string version number.
 */
function rocket_get_sg_optimizer_version() {
	static $version;

	if ( isset( $version ) ) {
		return $version;
	}

	$sg_optimizer = get_file_data( WP_PLUGIN_DIR . '/sg-cachepress/sg-cachepress.php', [ 'Version' => 'Version' ] );
	$version      = $sg_optimizer['Version'];

	return $version;
}

/**
 * Checks if SG Optimizer Supercache is active.
 *
 * @since  3.2.3.1
 * @author Remy Perona
 *
 * @return bool
 */
function rocket_is_supercacher_active() {
	if ( ! version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
		global $sg_cachepress_environment;

		return isset( $sg_cachepress_environment ) && $sg_cachepress_environment instanceof SG_CachePress_Environment && $sg_cachepress_environment->cache_is_enabled();
	}

	return (bool) get_option( 'siteground_optimizer_enable_cache', 0 );
}

/**
 * Call the cache server to purge the cache with SuperCacher (SiteGround).
 *
 * @since 2.3
 *
 * @return void
 */
function rocket_clean_supercacher() {
	if ( ! rocket_is_supercacher_active() ) {
		return;
	}

	if ( ! version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
		SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
	} elseif ( isset( $sg_cachepress_supercacher ) && $sg_cachepress_supercacher instanceof SG_CachePress_Supercacher ) {
		$sg_cachepress_supercacher->purge_cache();
	}
}

/**
 * Clean WP Rocket cache when cleaning SG cache
 *
 * @return void
 */
function rocket_sg_clear_cache() {
	if ( empty( $_GET['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'sg-cachepress-purge' ) ) {
		return;
	}

	if ( ! current_user_can( 'rocket_purge_cache' ) ) {
		return;
	}

	rocket_clean_domain();
}

if ( rocket_is_supercacher_active() ) {
	add_action( 'admin_post_sg-cachepress-purge', 'rocket_sg_clear_cache', 0 );
	add_action( 'rocket_after_clean_domain', 'rocket_clean_supercacher' );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	// Prevent mandatory cookies on hosting with server cache.
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

	/**
	 * Force WP Rocket caching on SG Optimizer versions before 4.0.5.
	 *
	 * @since  3.0.4
	 * @author Arun Basil Lal
	 *
	 * @link   https://github.com/wp-media/wp-rocket/issues/925
	 */
	if ( version_compare( rocket_get_sg_optimizer_version(), '4.0.5' ) < 0 ) {
		add_filter( 'do_rocket_generate_caching_files', '__return_true', 11 );
	}

	if ( version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
		add_action( 'wp_ajax_sg-cachepress-purge', 'rocket_sg_clear_cache', 0 );
	} else {
		add_action( 'wp_ajax_admin_bar_purge_cache', 'rocket_sg_clear_cache', 0 );
	}
}
