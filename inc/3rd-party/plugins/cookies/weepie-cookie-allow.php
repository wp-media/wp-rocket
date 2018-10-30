<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Compatibility with WeePie Cookie Allow
 *
 * @since 2.9
 */
if ( class_exists( 'WpieCookieAllow' ) ) :
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_weepie_cookie_allow_mandatory_cookie' );
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_weepie_cookie_allow_dynamic_cookies' );
	add_action( 'update_option_wpca_settings_general', 'rocket_after_update_wp_cookie_allow_options', 10, 2 );
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
	add_filter( 'wpca_do_ob_start', '__return_false' );
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
function rocket_after_update_wp_cookie_allow_options( $old_value, $value ) {
	if ( ( isset( $old_value['general_plugin_status'], $value['general_plugin_status'] ) && ( $old_value['general_plugin_status'] === $value['general_plugin_status'] ) ) && isset( $old_value['general_cookies_before_consent'], $value['general_cookies_before_consent'] ) && $old_value['general_cookies_before_consent'] === $value['general_cookies_before_consent'] ) {
		return;
	}

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Update the config file.
	rocket_generate_config_file();
}

/**
 * Add cookies when we activate the plugin.
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_activate_wp_cookie_allow() {
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_weepie_cookie_allow_mandatory_cookie' );
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_weepie_cookie_allow_dynamic_cookies' );

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
	remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_weepie_cookie_allow_mandatory_cookie' );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_weepie_cookie_allow_dynamic_cookies' );

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
	if ( ! rocket_do_weepie_cookie_allow_cookies() ) {
		return $cookies;
	}

	$cookies[] = 'wpca_consent';

	return $cookies;
}

/**
 * Add the WeePie Cookie Allow cookies to the dynamic cookies list
 *
 * The WeePie Cookie Allow v3.2 or higher is required
 *
 * @uses rocket_do_weepie_cookie_allow_cookies()
 *
 * @since
 * @author
 *
 * @param array $cookies Cookies to use for dynamic caching.
 * @return array Updated cookies list
 */
function rocket_add_weepie_cookie_allow_dynamic_cookies( $cookies ) {
	if ( ! rocket_do_weepie_cookie_allow_cookies() || version_compare( WpieCookieAllow::VERSION, '3.2' ) < 0 ) {
		return $cookies;
	}

	$cookies[] = 'wpca_cc';
	$cookies[] = 'wpca_consent';

	return $cookies;
}

/**
 * Determine if WeePie Cookie Allow cookies should be added to the dynamic or mandatory lists
 *
 * @uses rocket_get_weepie_cookie_allow_options()
 *
 * @since
 * @author
 *
 * @return bool
 */
function rocket_do_weepie_cookie_allow_cookies() {
	$options = rocket_get_weepie_cookie_allow_options();

	if ( ! isset( $options['general_plugin_status'] ) || 1 !== (int) $options['general_plugin_status'] ) {
		return false;
	}

	if ( ! isset( $options['general_cookies_before_consent'] ) || 3 === (int) $options['general_cookies_before_consent'] ) {
		return false;
	}

	return true;
}

/**
 * Get WeePie Cookie Allow Options array
 *
 * If WPML is active, check the options array for the current language locale: array( 'en_US' => array( ... ), 'nl_NL' => array( ... ), etc )
 *
 * @since
 * @author
 *
 * @return array
 */
function rocket_get_weepie_cookie_allow_options() {
	$options = get_option( 'wpca_settings_general' );

	// check if settings are indexed by multilang locales.
	if ( ! rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		return $options;
	}

	$wpml_current_language = apply_filters( 'wpml_current_language', false );

	if ( ! $wpml_current_language ) {
		return $options;
	}

	$wpml_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );

	if ( ! isset( $wpml_languages[ $wpml_current_language ]['default_locale'] ) ) {
		return $options;
	}

	$wpml_locale = $wpml_languages[ $wpml_current_language ]['default_locale'];

	if ( ! isset( $options[ $wpml_locale ] ) ) {
		return $options;
	}

	if ( ! is_array( $options[ $wpml_locale ] ) ) {
		return array();
	}

	return $options[ $wpml_locale ];
}
