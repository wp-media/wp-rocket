<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_action( 'update_option_secupress_users-login_settings', 'rocket_after_update_single_options', 10, 2 );

/**
 * Add SecuPress move login pages to cache exclusion
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param array $urls URLs to exclude from cache.
 * @return array Updated URLs to exclude
 */
function rocket_exclude_secupress_move_login( $urls ) {
	if ( ! function_exists( 'secupress_move_login_get_slugs' ) ) {
		return $urls;
	}

	$bases = secupress_get_rewrite_bases();
	$slugs = secupress_move_login_get_slugs();

	foreach ( $slugs as $slug ) {
		$urls[] = $bases['base'] . ltrim( $bases['site_from'], '/' ) . $slug . '/?';
	}

	return $urls;
}
add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

/**
 * Add SecuPress move login pages to cache exclusion when activating the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_maybe_activate_secupress() {
	if ( function_exists( 'secupress_move_login_get_slugs' ) ) {
		rocket_activate_secupress();
	}
}
add_action( 'secupress.plugins.activation', 'rocket_maybe_activate_secupress', 10001 );

/**
 * Add SecuPress move login pages to cache exclusion when activating the move login module
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_activate_secupress() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'secupress.plugin.move_login.activate', 'rocket_activate_secupress' );

/**
 * Remove SecuPress move login pages from cache exclusion when deactivating the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_maybe_deactivate_secupress() {
	if ( function_exists( 'secupress_move_login_get_slugs' ) ) {
		rocket_deactivate_secupress();
	}
}
add_action( 'secupress.deactivation', 'rocket_maybe_deactivate_secupress', 10001 );

/**
 * Remove SecuPress move login pages from cache exclusion when deactivating the move login module
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_deactivate_secupress() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'secupress.plugin.move_login.deactivate', 'rocket_deactivate_secupress' );
