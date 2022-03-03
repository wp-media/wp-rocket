<?php

defined( 'ABSPATH' ) || exit;

use WP_Rocket\Engine\Preload\FullProcess;
use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Engine\Preload\Sitemap;

/**
 * Launches the Homepage preload (helper function for backward compatibility)
 *
 * @since 2.6.4 Don't preload localhost & .dev domains
 * @since 1.0
 *
 * @param string $spider (default: 'cache-preload') The spider name: cache-preload or cache-json.
 * @param string $lang (default: '') The language code to preload.
 *
 * @return bool Status of preload.
 */
function run_rocket_bot( $spider = 'cache-preload', $lang = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( ! get_rocket_option( 'manual_preload' ) ) {
		return false;
	}

	$urls = [];

	if ( ! $lang ) {
		$urls = get_rocket_i18n_uri();
	} else {
		$urls[] = get_rocket_i18n_home_url( $lang );
	}

	$homepage_preload = new Homepage( new FullProcess() );

	$homepage_preload->preload( $urls );

	return true;
}

/**
 * Launches the sitemap preload (helper function for backward compatibility)
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @return void
 */
function run_rocket_sitemap_preload() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( ! get_rocket_option( 'sitemap_preload' ) || ! get_rocket_option( 'manual_preload' ) ) {
		return;
	}

	/**
	 * Filters the sitemaps list to preload
	 *
	 * @since 2.8
	 *
	 * @param array Array of sitemaps URL
	 */
	$sitemaps = apply_filters( 'rocket_sitemap_preload_list', get_rocket_option( 'sitemaps', false ) );
	$sitemaps = array_flip( array_flip( $sitemaps ) );

	if ( ! $sitemaps ) {
		return;
	}

	$sitemap_preload = new Sitemap( new FullProcess() );

	$sitemap_preload->run_preload( $sitemaps );
}

/**
 * Launches the preload cache from the admin bar or the dashboard button
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0 (delete in 1.1.6 and re-add in 1.1.9)
 * @deprecated 3.2
 */
function do_admin_post_rocket_preload_cache() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( empty( $_GET['_wpnonce'] ) ) {
		wp_safe_redirect( wp_get_referer() );
		die();
	}

	if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'preload' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'rocket_preload_cache' ) ) {
		wp_safe_redirect( wp_get_referer() );
		die();
	}

	$preload_process = new FullProcess();

	if ( $preload_process->is_process_running() ) {
		wp_safe_redirect( wp_get_referer() );
		die();
	}

	delete_transient( 'rocket_preload_errors' );

	$lang = isset( $_GET['lang'] ) && 'all' !== $_GET['lang'] ? sanitize_key( $_GET['lang'] ) : '';
	run_rocket_bot( 'cache-preload', $lang );
	run_rocket_sitemap_preload();

	if ( ! strpos( wp_get_referer(), 'wprocket' ) ) {
		set_transient( 'rocket_preload_triggered', 1 );
	}

	wp_safe_redirect( wp_get_referer() );
	die();
}
add_action( 'admin_post_nopriv_preload', 'do_admin_post_rocket_preload_cache' );
add_action( 'admin_post_preload', 'do_admin_post_rocket_preload_cache' );
