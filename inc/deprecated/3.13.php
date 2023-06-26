<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add cookies when we activate any goetargetingWP plugin.
 *
 * @since 2.10.3
 * @author Damian Logghe
 */
function rocket_activate_geotargetingwp() {
	_deprecated_function( __FUNCTION__ . '()', '3.13.3' );
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 72 );
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_geotargetingwp_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_geotargetingwp_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}

/**
 * Remove cookies when we deactivate the plugin.
 *
 * @since 2.10.3
 * @author Damian Logghe
 */
function rocket_deactivate_geotargetingwp() {
	_deprecated_function( __FUNCTION__ . '()', '3.13.3' );
	// add into db a record saying we deactivated one of the family plugins.
	update_option( 'geotWP-deactivated', true );
	remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 72 );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_geotargetingwp_dynamic_cookies' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_geotargetingwp_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}

/**
 * Add the GeotargetingWP cookies to generate caching files depending on their values.
 *
 * @since 2.10.3
 * @author Damian Logghe
 *
 * @param Array $cookies An array of cookies.
 * @return Array Updated array of cookies
 */
function rocket_add_geotargetingwp_dynamic_cookies( $cookies ) {
	_deprecated_function( __FUNCTION__ . '()', '3.13.3' );
	return rocket_add_geot_cookies( $cookies );
}

/**
 * Add the GeotargetingWP cookies to the list of mandatory cookies before to generate caching files.
 *
 * @since 2.10.3
 * @author Damian Logghe
 *
 * @param Array $cookies An array of cookies.
 * @return Array Updated array of cookies
 */
function rocket_add_geotargetingwp_mandatory_cookie( $cookies ) {
	_deprecated_function( __FUNCTION__ . '()', '3.13.3' );
	return rocket_add_geot_cookies( $cookies );
}

/**
 * Let users modify cache level by default set to country.
 *
 * @since 2.10.3
 * @author Damian Logghe
 *
 * @param Array $cookies An array of cookies.
 * @return Array Updated array of cookies
 */
function rocket_add_geot_cookies( $cookies ) {
	_deprecated_function( __FUNCTION__ . '()', '3.13.3' );
	// valid options are country, state, city.
	$enabled_cookies = apply_filters( 'rocket_geotargetingwp_enabled_cookies', [ 'country' ] );
	foreach ( $enabled_cookies as $enabled_cookie ) {
		if ( ! in_array( 'geot_rocket_' . $enabled_cookie, $cookies, true ) ) {
			$cookies[] = 'geot_rocket_' . $enabled_cookie;
		}
	}
	return $cookies;
}
