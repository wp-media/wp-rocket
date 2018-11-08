<?php
/**
 * Compatibility with UK Cookie Consent.
 *
 * @since  3.2
 * @author TheZoker
 * @link   https://wordpress.org/plugins/uk-cookie-consent/
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( class_exists( 'CTCC_Public' ) ) {
	add_filter( 'rocket_htaccess_mod_rewrite'    , '__return_false' );
	// Create cache version based on value set in cookie_consent_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_consent_cookie' );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_get_cookie_consent_cookie' );
}

/**
 * Return the cookie name set by Cookie Consent plugin.
 *
 * @since  3.2
 * @author TheZoker
 *
 * @param  array $cookies List of dynamic cookies.
 * @return array          List of dynamic cookies with the Cookie Consent cookie appended.
 */
function rocket_get_cookie_consent_cookie( $cookies ) {
	$cookies[] = 'catAccCookies';
	return $cookies;
}

/**
 * Add dynamic cookie and mandatory cookie when Cookie Consent plugin is activated.
 *
 * @since  3.2
 * @author TheZoker
 */
function rocket_add_cookie_consent_dynamic_cookie() {
	add_filter( 'rocket_htaccess_mod_rewrite'    , '__return_false' );
	// Create cache version based on value set in cookie_consent_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_consent_cookie' );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_get_cookie_consent_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'activate_cookie-consent/cookie-consent.php', 'rocket_add_cookie_consent_dynamic_cookie', 11 );

/**
 * Remove dynamic cookie when Cookie Consent plugin is deactivated.
 *
 * @since  3.2
 * @author TheZoker
 */
function rocket_remove_cookie_consent_dynamic_cookie() {
	remove_filter( 'rocket_htaccess_mod_rewrite',    '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_consent_cookie' );
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_get_cookie_consent_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'deactivate_cookie-consent/cookie-consent.php', 'rocket_remove_cookie_consent_dynamic_cookie', 11 );
