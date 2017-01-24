<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with EU Cookie Law
 * https://wordpress.org/plugins/eu-cookie-law/
 *
 * @since 2.7
 */
if ( function_exists( 'eucookie_start' ) ) :
	/*
	 * Update .htaccess & config files when the "Activate" and "Autoblock" options are turned on
	 *
	 */
    add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_eu_cookie_law_mandatory_cookie' );
	add_action( 'update_option_peadig_eucookie', '__rocket_after_update_eu_cookie_law_options', 10, 2 );
	function __rocket_after_update_eu_cookie_law_options( $old_value, $value ) {
        if ( ( isset( $old_value['enabled'], $value['enabled'] ) && ( $old_value['enabled'] == $value['enabled'] ) ) && isset( $old_value['autoblock'], $value['autoblock'] ) && $old_value['autoblock'] == $value['autoblock'] ) {
            return;
        }

        // Update the WP Rocket rules on the .htaccess file
        flush_rocket_htaccess();
        
        // Update the config file
        rocket_generate_config_file();
	}

	// Don't add the WP Rocket rewrite rules to avoid issues
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false' );
endif;

// Add cookies when we activate the plugin
add_action( 'activate_eu-cookie-law/eu-cookie-law.php', '__rocket_activate_eu_cookie_law', 11 );
function __rocket_activate_eu_cookie_law() {
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_eu_cookie_law_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove cookies when we deactivate the plugin
add_action( 'deactivate_eu-cookie-law/eu-cookie-law.php', '__rocket_deactivate_eu_cookie_law', 11 );
function __rocket_deactivate_eu_cookie_law() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_mandatory_cookies', '_rocket_add_eu_cookie_law_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Add the EU Cookie Law to the list of mandatory cookies before generating caching files
function _rocket_add_eu_cookie_law_mandatory_cookie( $cookies ) {
	$options = get_option( 'peadig_eucookie' );

	if ( ! empty( $options['enabled'] ) && ! empty( $options['autoblock'] ) ) {
		$cookies['eu-cookie-law'] = 'euCookie';
	}

	return $cookies;
}