<?php

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility with WeePie Cookie Allow
 *
 * @since 2.9
 */
if ( class_exists( 'WpieCookieAllow' ) ) :
	add_action( 'update_option_wpca_settings_general', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );
	add_action( 'update_option_wpca_settings_style', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );
	add_action( 'update_option_wpca_settings_content', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );
	add_action( 'update_option_wpca_settings_consent_log', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );
endif;

/**
 * Update .htaccess & config files when the "Enabled" and "Autoblock" options are turned on.
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param array $old_value Previous values for the plugin options.
 * @param array $value New values for the plugin options.
 */
function rocket_after_update_wp_cookie_allow_options( $old_value, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	// clear the cache because WeePie Cookie Allow plugin settings might have been changed.
	rocket_clean_domain();
}

/**
 * Add cookies when we activate the plugin.
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_activate_wp_cookie_allow() {
	// clear the cache because plugin might be enabled already before.
	rocket_clean_domain();
}
add_action( 'activate_wp-cookie-allow/wp-cookie-allow.php', 'rocket_activate_wp_cookie_allow', 11 );

/**
 * Remove cookies when we deactivate the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_deactivate_wp_cookie_allow() {
	// clear the cache because the bar/box and other WeePie Cookie Allow plugin frontend HTML is not needed anymore.
	rocket_clean_domain();
}
add_action( 'deactivate_wp-cookie-allow/wp-cookie-allow.php', 'rocket_deactivate_wp_cookie_allow', 11 );
