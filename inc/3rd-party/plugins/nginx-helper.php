<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'rtCamp\WP\Nginx\RT_WP_NGINX_HELPER_PATH' ) ) :
	/**
	 * Run WP Rocket preload bot after the cache is purged from Nginx Helper.
	 *
	 * @since 2.x.x
	 *
	 * @return void
	 */
	function rocket_run_rocket_bot_after_wpengine() {
		if ( isset( $_GET['nginx_helper_action'] ) && check_admin_referer( 'nginx_helper-purge_all' ) ) {
			// Preload cache.
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
	add_action( 'rt_nginx_helper_purge_all', 'rocket_run_rocket_bot_after_wpengine' );

	/**
	 * Clean the cache using Nginx Helper after WP Rocket's cache is purged.
	 *
	 * @since 2.x.x
	 */
	function rocket_clean_wpengine() {
		global $rt_wp_nginx_purger;
		if ( method_exists( $rt_wp_nginx_purger, 'true_purge_all' ) ) {
			do_action( 'rt_nginx_helper_purge_all' );
		}
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_wpengine' );
endif;
