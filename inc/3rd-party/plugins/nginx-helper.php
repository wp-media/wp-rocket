<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

global $nginx_helper;

if ( isset( $nginx_helper ) ) :
	global $nginx_purger;

	/**
	 * Clear WP Rocket cache after the NGINX cache is purged from Nginx Helper.
	 *
	 * @since 3.3.0.1
	 *
	 * @return void
	 */
	function rocket_clear_cache_after_nginx_helper_purge() {
		if ( ! isset( $_GET['nginx_helper_action'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'nginx_helper-purge_all' ) ) {
			return;
		}

		if ( 'done' !== sanitize_text_field( wp_unslash( $_GET['nginx_helper_action'] ) ) ) {
			return;
		}

		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		// Clear all caching files.
		rocket_clean_domain();
	}
	add_action( 'admin_init', 'rocket_clear_cache_after_nginx_helper_purge' );

	/**
	 * Clear WP Rocket cache for the current page after the NGINX cache is purged from Nginx Helper.
	 *
	 * @since 3.3.0.1
	 *
	 * @return void
	 */
	function rocket_clear_current_page_after_nginx_helper_purge() {
		if ( ! isset( $_GET['nginx_helper_action'], $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'nginx_helper-purge_all' ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		$referer = wp_get_referer();

		if ( 0 !== strpos( $referer, 'http' ) ) {
			$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
			$referer   = $parse_url['scheme'] . '://' . $parse_url['host'] . $referer;
		}

		if ( home_url( '/' ) === $referer ) {
			rocket_clean_home();
			return;
		}

		rocket_clean_files( $referer );
	}
	add_action( 'init', 'rocket_clear_current_page_after_nginx_helper_purge' );

	/**
	 * Clears NGINX cache for the homepage URL when using "Purge this URL" from the admin bar on the front end
	 *
	 * @since 3.3.0.1
	 * @author Remy Perona
	 *
	 * @param string $root WP Rocket root cache path.
	 * @param string $lang Current language.
	 * @return void
	 */
	function rocket_clean_nginx_cache_home( $root = '', $lang = '' ) {
		global $nginx_purger;

		if ( ! isset( $nginx_purger ) ) {
			return;
		}

		$url = get_rocket_i18n_home_url( $lang );

		$nginx_purger->purge_url( $url );
	}
	add_action( 'after_rocket_clean_home', 'rocket_clean_nginx_cache_home', 10, 2 );

	/**
	 * Clears NGINX cache for a specific URL when using "Purge this URL" from the admin bar on the front end
	 *
	 * @since 3.3.0.1
	 * @author Remy Perona
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	function rocket_clean_nginx_cache_url( $url ) {
		global $nginx_purger;

		if ( ! isset( $nginx_purger ) ) {
			return;
		}

		if ( ! isset( $_GET['type'], $_GET['_wpnonce'] ) ) { // WPCS: csrf ok.
			return;
		}

		if ( false !== strpos( $url, 'index.html' ) ) {
			return;
		}

		if ( 'page' === substr( $url, -4 ) ) {
			return;
		}

		$url = str_replace( '*', '', $url );

		$nginx_purger->purge_url( $url );
	}
	add_action( 'after_rocket_clean_file', 'rocket_clean_nginx_cache_url' );

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
