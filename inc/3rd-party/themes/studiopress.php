<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

add_action( 'admin_init', 'rocket_clear_cache_after_studiopress_accelerator' );
/**
 * Clear WP Rocket cache after purged the StudioPress Accelerator cache
 *
 * @since 2.5.5
 *
 * @return void
 */
function rocket_clear_cache_after_studiopress_accelerator() {
	if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) && isset( $_REQUEST['_wpnonce'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'sp-accel-purge-url' ) && ! empty( $_REQUEST['cache-purge-url'] ) ) {
			$submitted_url = $_REQUEST['cache-purge-url'];

			// Clear the URL.
			rocket_clean_files( array( $submitted_url ) );
		} elseif ( wp_verify_nonce( $nonce, 'sp-accel-purge-theme' ) ) {
			// Clear all caching files.
			rocket_clean_domain();

			// Preload cache.
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
}

add_action( 'after_rocket_clean_domain', 'rocket_clean_studiopress_accelerator' );
/**
 * Call the cache server to purge the cache with StudioPress Accelerator.
 *
 * @since 2.5.5
 *
 * @return void
 */
function rocket_clean_studiopress_accelerator() {
	if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) ) {
		$GLOBALS['sp_accel_nginx_proxy_cache_purge']->cache_flush_theme();
	}
}
