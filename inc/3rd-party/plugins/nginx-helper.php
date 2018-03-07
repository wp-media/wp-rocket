<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'rtCamp\WP\Nginx\RT_WP_NGINX_HELPER_PATH' ) ) :
	add_action( 'admin_init', 'rocket_clear_cache_after_nginx_helper_purge' );
	/**
	 * Clear WP Rocket cache after the cache is purged from Nginx Helper.
	 *
	 * @since 2.x.x
	 *
	 * @return void
	 */
	function rocket_clear_cache_after_nginx_helper_purge() {
		if ( isset( $_GET['nginx_helper_action'] ) && 'done' == $_GET['nginx_helper_action'] && current_user_can( 'manage_options' ) && check_admin_referer( 'nginx_helper-purge_all' ) ) {
			// Clear all caching files.
			rocket_clean_domain();

			// Preload cache.
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
	/**
	 * Clean the cache using Nginx Helper after WP Rocket's cache is purged.
	 *
	 * @since 2.x.x
	 */
	function rocket_clean_nginx_helper_cache() {
		do_action( 'rt_nginx_helper_purge_all' );
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_nginx_helper_cache' );
endif;
