<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Compatibility with GeotargetingWP Plugins
 *
 * @author Damian Logghe <info@timersys.com>
 */
if ( class_exists( 'GeotWP\GeotargetingWP' ) ) :

	add_filter( 'rocket_htaccess_mod_rewrite'    , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'   , 'rocket_add_geotargetingwp_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies' , 'rocket_add_geotargetingwp_mandatory_cookie' );

	/**
	 * If we recently deactivated a plugin of the family but
	 * we still see the class it means another plugin is still active,
	 * so flush rules once more to be safe
	 */
	if ( get_option( 'geotWP-deactivated' ) ) {
		// Update the WP Rocket rules on the .htaccess file.
		add_action( 'admin_init', 'flush_rocket_htaccess' );

		// Regenerate the config file.
		add_action( 'admin_init', 'rocket_generate_config_file' );
		delete_option( 'geotWP-deactivated' );
	}
endif;

/**
 * Add cookies when we activate any goetargetingWP plugin.
 *
 * @since 2.10.3
 * @author Damian Logghe
 */
function rocket_activate_geotargetingwp() {
	add_filter( 'rocket_htaccess_mod_rewrite'    , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'   , 'rocket_add_geotargetingwp_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies' , 'rocket_add_geotargetingwp_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'geotWP/activated', 'rocket_activate_geotargetingwp', 11 );

/**
 * Remove cookies when we deactivate the plugin.
 *
 * @since 2.10.3
 * @author Damian Logghe
 */
function rocket_deactivate_geotargetingwp() {
	// add into db a record saying we deactivated one of the family plugins.
	update_option( 'geotWP-deactivated', true );
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_geotargetingwp_dynamic_cookies' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_geotargetingwp_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'geotWP/deactivated', 'rocket_deactivate_geotargetingwp', 11 );

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
	// valid options are country, state, city.
	$enabled_cookies = apply_filters( 'rocket_geotargetingwp_enabled_cookies' , array( 'country' ) );
	foreach ( $enabled_cookies as $enabled_cookie ) {
		if ( ! in_array( 'geot_rocket_' . $enabled_cookie, $cookies, true ) ) {
			$cookies[] = 'geot_rocket_' . $enabled_cookie;
		}
	}
	return $cookies;
}
