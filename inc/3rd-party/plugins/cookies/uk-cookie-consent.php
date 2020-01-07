<?php
/**
 * Compatibility with UK Cookie Consent.
 *
 * @since  3.2
 * @author TheZoker
 * @link   https://wordpress.org/plugins/uk-cookie-consent/
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'CTCC_Public' ) ) {
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 62 );
	// Create cache version based on value set in cookie_consent_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_get_cookie_uk_consent_cookie' );
}

/**
 * Return the cookie name set by UK Cookie Consent plugin.
 *
 * @since  3.2
 * @author TheZoker
 *
 * @param  array $cookies List of dynamic cookies.
 * @return array          List of dynamic cookies with the UK Cookie Consent cookie appended.
 */
function rocket_get_cookie_uk_consent_cookie( $cookies ) {
	$cookies[] = 'catAccCookies';
	return $cookies;
}

/**
 * Add dynamic cookie and mandatory cookie when UK Cookie Consent plugin is activated.
 *
 * @since  3.2
 * @author TheZoker
 */
function rocket_add_uk_cookie_consent_dynamic_cookie() {
	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 62 );
	// Create cache version based on value set in cookie_consent_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_get_cookie_uk_consent_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'activate_uk-cookie-consent/uk-cookie-consent.php', 'rocket_add_uk_cookie_consent_dynamic_cookie', 11 );

/**
 * Remove dynamic cookie when Cookie Consent plugin is deactivated.
 *
 * @since  3.2
 * @author TheZoker
 */
function rocket_remove_uk_cookie_consent_dynamic_cookie() {
	remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 62 );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_get_cookie_uk_consent_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'deactivate_uk-cookie-consent/uk-cookie-consent.php', 'rocket_remove_uk_cookie_consent_dynamic_cookie', 11 );
