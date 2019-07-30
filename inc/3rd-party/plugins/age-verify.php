<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Age Verify: don't cache pages until the age-verified cookie is set
 *
 * @since 2.7
 */
if ( class_exists( 'Age_Verify' ) && defined( 'Age_Verify::SLUG' ) ) :
	add_filter( 'rocket_htaccess_mod_rewrite'   , '__return_false', 18 );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_cache_mandatory_cookie_for_age_verify' );
endif;

/**
 * Add age-verified to the list of mandatory cookies
 *
 * @since 2.7
 *
 * @param Array $cookies Array of mandatory cookies.
 * @return Array Updated array of mandatory cookies
 */
function rocket_add_cache_mandatory_cookie_for_age_verify( $cookies ) {
	$cookies[] = 'age-verified';
	return $cookies;
}

/**
 * Add age-verified cookie when we activate the plugin
 *
 * @since 2.7
 */
function rocket_activate_age_verify() {
	add_filter( 'rocket_htaccess_mod_rewrite'   , '__return_false', 18 );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_cache_mandatory_cookie_for_age_verify' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_age-verify/age-verify.php', 'rocket_activate_age_verify', 11 );

/**
 * Remove age-verified cookie when we deactivate the plugin
 *
 * @since 2.7
 */
function rocket_deactivate_age_verify() {
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_cache_mandatory_cookie_for_age_verify' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_age-verify/age-verify.php', 'rocket_deactivate_age_verify', 11 );
