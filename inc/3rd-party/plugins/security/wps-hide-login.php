<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( class_exists( 'WPS_Hide_Login' ) ) :
	add_action( 'update_option_whl_page', 'rocket_after_update_single_options', 10, 2 );
	add_filter( 'rocket_cache_reject_uri', 'rocket_exlude_wps_hide_login_page' );
endif;

/**
 * Exclude WPS Hide Login custom url from caching
 *
 * @since 2.11 Moved to 3rd party file
 * @since 2.6
 *
 * @param array $urls An array of URLs to exclude from cache.
 * @return array Updated array of URLs
 */
function rocket_exlude_wps_hide_login_page( $urls ) {
	$urls[] = rocket_clean_exclude_file( home_url( trailingslashit( get_option( 'whl_page' ) ) ) );

	return $urls;
}

/**
 * Add WPS Hide Login custom url to caching exclusion when activating the plugin
 *
 * @since 2.11
 */
function rocket_activate_wps_hide_login() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exlude_wps_hide_login_page' );

	// Update .htaccess file rules.
	flush_rocket_htaccess();

	// Update config file.
	rocket_generate_config_file();
}
add_action( 'activate_wps-hide-login/wps-hide-login.php', 'rocket_activate_wps_hide_login', 11 );

/**
 * Remove WPS Hide Login custom url from caching exclusion when deactivating the plugin
 *
 * @since 2.11
 */
function rocket_deactivate_wps_hide_login() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exlude_wps_hide_login_page' );

	// Update .htaccess file rules.
	flush_rocket_htaccess();

	// Update config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_wps-hide-login/wps-hide-login.php', 'rocket_deactivate_wps_hide_login', 11 );
