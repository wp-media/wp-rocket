<?php

defined( 'ABSPATH' ) || exit;

/**
 * Changes the text on the Varnish one-click block.
 *
 * @since  3.0
 * @author Remy Perona
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_flywheel_varnish_field( $settings ) {
	$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
		__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
		'Flywheel'
	);

	return $settings;
}
add_filter( 'rocket_varnish_field_settings', 'rocket_flywheel_varnish_field' );

add_filter( 'rocket_display_input_varnish_auto_purge', '__return_false' );

/**
 * Allow to purge Varnish on Flywheel websites
 *
 * @since 2.6.8
 */
add_filter( 'do_rocket_varnish_http_purge', '__return_true' );
add_filter( 'do_rocket_generate_caching_files', '__return_false' );

// Prevent mandatory cookies on hosting with server cache.
add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

/**
 * Set up the right Varnish IP for Flywheel
 *
 * @since 2.6.8
 * @param array $varnish_ip Varnish IP.
 */
function rocket_varnish_ip_on_flywheel( $varnish_ip ) {
	$varnish_ip[] = '127.0.0.1';

	return $varnish_ip;
}
add_filter( 'rocket_varnish_ip', 'rocket_varnish_ip_on_flywheel' );

/**
 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
 *
 * @since  3.3.1
 * @author Remy Perona
 *
 * @return void
 */
function rocket_flywheel_remove_partial_purge_hooks() {
	// WP core action hooks rocket_clean_post() gets hooked into.
	$clean_post_hooks = [
		// Disables the refreshing of partial cache when content is edited.
		'wp_trash_post',
		'delete_post',
		'clean_post_cache',
		'wp_update_comment_count',
	];

	// Remove rocket_clean_post() from core action hooks.
	array_map(
		function( $hook ) {
			remove_action( $hook, 'rocket_clean_post' );
		},
		$clean_post_hooks
	);

	remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
}
add_action( 'wp_rocket_loaded', 'rocket_flywheel_remove_partial_purge_hooks' );
