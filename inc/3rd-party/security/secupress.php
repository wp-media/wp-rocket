<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_action( 'update_option_secupress_users-login_settings', '__rocket_after_update_single_options', 10, 2 );


add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

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


add_action( 'secupress.plugins.activation', 'rocket_maybe_activate_secupress', 10001 );

function rocket_maybe_activate_secupress() {
	if ( function_exists( 'secupress_move_login_get_slugs' ) ) {
		rocket_activate_secupress();
	}
}


add_action( 'secupress.plugin.move_login.activate', 'rocket_activate_secupress' );

function rocket_activate_secupress() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}


add_action( 'secupress.deactivation', 'rocket_maybe_deactivate_secupress', 10001 );

function rocket_maybe_deactivate_secupress() {
	if ( function_exists( 'secupress_move_login_get_slugs' ) ) {
		rocket_deactivate_secupress();
	}
}


add_action( 'secupress.plugin.move_login.deactivate', 'rocket_deactivate_secupress' );

function rocket_deactivate_secupress() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_secupress_move_login' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}