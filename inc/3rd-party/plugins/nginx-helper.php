<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

global $nginx_helper;

if ( isset( $nginx_helper ) ) :
	/**
	 * Clear WP Rocket cache after the NGINX cache is purged from Nginx Helper.
	 *
	 * @since 3.3.0.1
	 *
	 * @return void
	 */
	function rocket_clear_cache_after_nginx_helper_purge() {
		if ( ! isset( $_GET['nginx_helper_action'], $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'nginx_helper-purge_all' ) ) {
			return;
		}

		if ( 'done' !== sanitize_text_field( wp_unslash( $_GET['nginx_helper_action'] ) ) ) {
			return;
		}

		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( ! check_admin_referer( 'nginx_helper-purge_all' ) ) {
			return;
		}

		// Clear all caching files.
		rocket_clean_domain();
	}
	add_action( 'admin_init', 'rocket_clear_cache_after_nginx_helper_purge' );

	/**
	 * Clean the NGINX cache using Nginx Helper after WP Rocket's cache is purged.
	 *
	 * @since 3.3.0.1
	 */
	function rocket_clean_nginx_helper_cache() {
		if ( isset( $_GET['nginx_helper_action'] ) ) { // WPCS: csrf ok.
			return;
		}

		do_action( 'rt_nginx_helper_purge_all' ); // WPCS: prefix ok.
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_nginx_helper_cache' );
endif;
