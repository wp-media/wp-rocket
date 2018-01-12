<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Compatibility with WeePie Cookie Allow
 *
 * @since 2.9
 */
if ( class_exists( 'WpieCookieAllow' ) ) :
	add_filter( 'rocket_cache_mandatory_cookies' , 'rocket_add_weepie_cookie_allow_mandatory_cookie' );

	/**
	 * Update .htaccess & config files when the "Enabled" and "Autoblock" options are turned on.
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous values for the plugin options.
	 * @param array $value New values for the plugin options.
	 */
	function rocket_after_update_wp_cookie_allow_options( $old_value, $value ) {
		if ( ( isset( $old_value['general_plugin_status'], $value['general_plugin_status'] ) && ( $old_value['general_plugin_status'] === $value['general_plugin_status'] ) ) && isset( $old_value['general_cookies_before_consent'], $value['general_cookies_before_consent'] ) && $old_value['general_cookies_before_consent'] === $value['general_cookies_before_consent'] ) {
			return;
		}

		// Update the WP Rocket rules on the .htaccess file.
		flush_rocket_htaccess();

		// Update the config file.
		rocket_generate_config_file();
	}
	add_action( 'update_option_wpca_settings_general', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );

	// Don't add the WP Rocket rewrite rules to avoid issues.
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
endif;

/**
 * Add cookies when we activate the plugin.
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_activate_wp_cookie_allow() {
	add_filter( 'rocket_htaccess_mod_rewrite'    , '__return_false' );
	add_filter( 'rocket_cache_mandatory_cookies' , 'rocket_add_weepie_cookie_allow_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_wp-cookie-allow/wp-cookie-allow.php', 'rocket_activate_wp_cookie_allow', 11 );

/**
 * Remove cookies when we deactivate the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_deactivate_wp_cookie_allow() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_weepie_cookie_allow_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_wp-cookie-allow/wp-cookie-allow.php', 'rocket_deactivate_wp_cookie_allow', 11 );

/**
 * Add the WeePie Cookie Allow cookie to the list of mandatory cookies before generating caching files.
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param array $cookies An array of mandatory cookies.
 * @return array Updated array
 */
function rocket_add_weepie_cookie_allow_mandatory_cookie( $cookies ) {
	$options = get_option( 'wpca_settings_general' );

	if ( 1 === (int) $options['general_plugin_status'] && 1 === (int) $options['general_cookies_before_consent'] ) {
		$cookies['weepie-cookie-allow'] = 'wpca_consent';
	}

	return $cookies;
}
