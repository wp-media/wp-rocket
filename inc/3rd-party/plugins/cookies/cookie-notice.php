<?php
/**
 * Compatibility with Cookie Notice by dFactory.
 *
 * @since  2.11.6
 * @author Arun Basil Lal
 * @link   https://wordpress.org/plugins/cookie-notice/
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( class_exists( 'Cookie_Notice' ) ) {
	// Don't add the WP Rocket rewrite rules to avoid issues.
	if ( ! class_exists( 'CC4R_options' ) || ! CC4R_options::rewrite_enabled() ) {
		add_filter( 'rocket_htaccess_mod_rewrite',    '__return_false' );
	}
	// Create cache version based on value set in cookie_notice_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_notice_cookie' );
}

/**
 * Return the cookie name set by Cookie Notice plugin.
 *
 * @since  2.11.6
 * @author Arun Basil Lal
 *
 * @param  array $cookies List of dynamic cookies.
 * @return array          List of dynamic cookies with the Cookie Notice cookie appended.
 */
function rocket_get_cookie_notice_cookie( $cookies ) {
	$cookies[] = 'cookie_notice_accepted';
	return $cookies;
}

/**
 * Add dynamic cookie and mandatory cookie when Cookie Notice plugin is activated.
 *
 * @since  2.11.6
 * @author Arun Basil Lal
 */
function rocket_add_cookie_notice_dynamic_cookie() {
	// Don't add the WP Rocket rewrite rules to avoid issues.
	if ( ! class_exists( 'CC4R_options' ) || ! CC4R_options::rewrite_enabled() ) {
		add_filter( 'rocket_htaccess_mod_rewrite',    '__return_false' );
	}
	// Create cache version based on value set in cookie_notice_accepted cookie.
	add_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_notice_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'activate_cookie-notice/cookie-notice.php', 'rocket_add_cookie_notice_dynamic_cookie', 11 );

/**
 * Remove dynamic cookie when Cookie Notice plugin is deactivated.
 *
 * @since  2.11.6
 * @author Arun Basil Lal
 */
function rocket_remove_cookie_notice_dynamic_cookie() {
	if ( ! class_exists( 'CC4R_options' ) || ! CC4R_options::rewrite_enabled() ) {
		remove_filter( 'rocket_htaccess_mod_rewrite',    '__return_false' );
	}
	remove_filter( 'rocket_cache_dynamic_cookies',   'rocket_get_cookie_notice_cookie' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();

	// Clear WP Rocket cache.
	rocket_clean_domain();
}
add_action( 'deactivate_cookie-notice/cookie-notice.php', 'rocket_remove_cookie_notice_dynamic_cookie', 11 );
