<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Conflict with Age Verify: don't cache pages until to have the age-verified cookie
 *
 * @since 2.7
 */
if ( class_exists( 'Age_Verify' ) && defined( 'Age_Verify::SLUG' ) ) :

add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
add_filter( 'rocket_cache_mandatory_cookies', '__rocket_add_cache_mandatory_cookie_for_age_verify' );

endif;

// Add age-verified to the list of mandatory cookies
function __rocket_add_cache_mandatory_cookie_for_age_verify( $cookies ) {
	$cookies[] = 'age-verified';
	return $cookies;
}

// Add age-verified cookie when we activate the plugin
add_action( 'activate_age-verify/age-verify.php'	, '__rocket_activate_age_verify', 11 );
function __rocket_activate_age_verify() {
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
	add_filter( 'rocket_cache_mandatory_cookies', '__rocket_add_cache_mandatory_cookie_for_age_verify' );
	
	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();
	
	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove age-verified cookie when we deactivate the plugin
add_action( 'deactivate_age-verify/age-verify.php'	, '__rocket_deactivate_age_verify', 11 );
function __rocket_deactivate_age_verify() {
	remove_filter( 'rocket_cache_mandatory_cookies', '__rocket_add_cache_mandatory_cookie_for_age_verify' );
	
	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();
	
	// Regenerate the config file
	rocket_generate_config_file();
}