<?php

defined( 'ABSPATH' ) || exit;

if ( defined( 'POLYLANG_VERSION' ) && POLYLANG_VERSION ) :
	/**
	 * Conflict with Polylang: Clear the whole cache when the "The language is set from content" option is activated.
	 *
	 * @since 2.6.8
	 */
	function rocket_force_clean_domain_on_polylang() {
		$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

		if ( isset( $pll ) && 0 === $pll->options['force_lang'] ) {
			rocket_clean_cache_dir();
		}
	}
	add_action( 'rocket_after_clean_domain', 'rocket_force_clean_domain_on_polylang' );

	// Filter mandatory cookies and WP Rocket rewrite rules if Polylang module 'Detect browser language' is enabled.
	// Read once and by key only: from Polylang 3.7 this is an object that answers like an array.
	$rocket_pll_options = function_exists( 'PLL' ) ? PLL()->options : [];

	if ( isset( $rocket_pll_options['browser'] ) && $rocket_pll_options['browser'] ) {

		// Add Polylang's language cookie as a mandatory cookie.
		add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_polylang_mandatory_cookie' );

		// The language is set from content, so the address does not carry it and the file name must.
		if ( isset( $rocket_pll_options['force_lang'] ) && 0 === (int) $rocket_pll_options['force_lang'] ) {
			add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );
		}

		// Remove WP Rocket rewrite rules from .htaccess file.
		add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 74 );
	}
endif;

/**
 * Add Polylang's language cookie to the mandatory cookies of WP Rocket.
 *
 * Polylang saves the users preferred language in this cookie by detecting browser language or by user choice
 * Adding this as a mandatory cookie prevents WP Rocket from serving the cache when the cookie is not set.
 *
 * @param array $cookies Array with mandatory cookies.
 * @return (array) Array of mandatory cookies with the Polylang cookie appended
 *
 * @author Arun Basil Lal
 * @since 3.0.5
 */
function rocket_add_polylang_mandatory_cookie( $cookies ) {
	$cookies[] = rocket_get_polylang_cookie_name();

	return $cookies;
}

/**
 * Whether the cache has to vary by the language cookie under these settings.
 *
 * @since 3.24
 *
 * @param array|ArrayAccess $settings Polylang settings.
 * @return bool
 */
function rocket_polylang_varies_by_cookie( $settings ) {
	return isset( $settings['browser'], $settings['force_lang'] )
		&& 1 === (int) $settings['browser']
		&& 0 === (int) $settings['force_lang'];
}

/**
 * Gets the name Polylang keeps the visitor's language under.
 *
 * A site can turn the cookie off with define( 'PLL_COOKIE', false ), and that is what comes back
 * then. The lists this feeds drop it, as they drop anything empty.
 *
 * @since 3.24
 *
 * @return string|false
 */
function rocket_get_polylang_cookie_name() {
	return defined( 'PLL_COOKIE' ) ? PLL_COOKIE : 'pll_language';
}

/**
 * Add Polylang's language cookie to the dynamic cookies of WP Rocket.
 *
 * @since 3.24
 *
 * @param array $cookies Array with dynamic cookies.
 * @return array Array of dynamic cookies with the Polylang cookie appended.
 */
function rocket_add_polylang_dynamic_cookie( $cookies ) {
	$cookies[] = rocket_get_polylang_cookie_name();

	return $cookies;
}

/**
 * Add mandatory cookie to WP Rocket config and remove rewrite rules from .htaccess on Polylang activation.
 *
 * Add mandatory cookie only if the Polylang module 'Detect browser language' is active.
 * Also purge the homepage cache, and the whole domain where the language enters the file name.
 *
 * @author Arun Basil Lal
 * @since 3.0.5
 */
function rocket_activate_polylang() {
	// Read Polylang settings from db.
	$polylang_settings = get_option( 'polylang' );

	$varies_by_cookie = rocket_polylang_varies_by_cookie( $polylang_settings );

	if ( isset( $polylang_settings['browser'] ) && ( 1 === (int) $polylang_settings['browser'] ) ) {
		// Add Polylang's language cookie as a mandatory cookie.
		add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_polylang_mandatory_cookie' );

		if ( $varies_by_cookie ) {
			add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );
		}

		// Remove WP Rocket rewrite rules from .htaccess file.
		add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 74 );

		// Regenerate the config file.
		rocket_generate_config_file();

		// Regenerate .htaccess file.
		flush_rocket_htaccess();

		// Purge homepage cache.
		rocket_clean_home();

		if ( $varies_by_cookie ) {
			// The language enters the file name, so what is cached under the old names goes.
			rocket_clean_domain();
		}
	}
}
add_action( 'activate_polylang/polylang.php', 'rocket_activate_polylang', 11 );

/**
 * Remove mandatory cookie and add rewrite rules back to .htaccess when Polylang is deactivated.
 *
 * Purge the whole domain where the language was part of the file name.
 *
 * @author Arun Basil Lal
 * @since 3.0.5
 */
function rocket_deactivate_polylang() {
	// Remove Polylang's language cookie as a mandatory cookie.
	remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_polylang_mandatory_cookie' );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );

	// Add back WP Rocket rewrite rules from .htaccess file.
	remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 74 );

	// Regenerate the config file.
	rocket_generate_config_file();

	// Regenerate .htaccess file.
	flush_rocket_htaccess();

	if ( rocket_polylang_varies_by_cookie( get_option( 'polylang' ) ) ) {
		// The language leaves the file name with the cookie, so the files under the old names go.
		rocket_clean_domain();
	}
}
add_action( 'deactivate_polylang/polylang.php', 'rocket_deactivate_polylang', 11 );

/**
 * Update mandatory cookie in WP Rocket config file and remove rewrite rules from .htaccess
 * when Detect browser language module is enabled / disabled.
 *
 * @param array $value     Array containing Polylang settings before its written to db.
 * @param array $old_value Array containing the Polylang settings being replaced, which is where the
 *                         previous state of the cache file names is read from.
 * @return array
 *
 * @author Arun Basil Lal
 * @since 3.0.5
 */
function rocket_detect_browser_language_status_change( $value, $old_value = [] ) {
	// Polylang changes its own copy of the settings before it saves them, so this reads the settings
	// being saved. Read once and by key only: from 3.7 it is an object that answers like an array.
	$polylang_settings = function_exists( 'PLL' ) ? PLL()->options : [];

	// The hook fires on every save, so the purge below asks whether the file names actually move.
	// Only the settings being replaced can say where they moved from, and they come from the hook.
	$renames_cache_files = rocket_polylang_varies_by_cookie( $old_value ) !== rocket_polylang_varies_by_cookie( $polylang_settings );

	if ( isset( $polylang_settings['browser'] ) && $polylang_settings['browser'] ) {

		// Add Polylang's language cookie as a mandatory cookie.
		add_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_polylang_mandatory_cookie' );

		if ( isset( $polylang_settings['force_lang'] ) && 0 === (int) $polylang_settings['force_lang'] ) {
			add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );
		} else {
			remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );
		}

		// Remove WP Rocket rewrite rules from .htaccess file.
		add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 74 );

		// Regenerate the config file.
		rocket_generate_config_file();

		// Regenerate .htaccess file.
		flush_rocket_htaccess();

		// Purge homepage cache.
		rocket_clean_home();

		if ( $renames_cache_files ) {
			// Every file is about to be looked for under another name, so the old ones go.
			rocket_clean_domain();
		}
	} else {
		// Remove Polylang's language cookie as a mandatory cookie.
		remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_add_polylang_mandatory_cookie' );
		remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_polylang_dynamic_cookie' );

		// Add back WP Rocket rewrite rules from .htaccess file.
		remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 74 );

		// Regenerate the config file.
		rocket_generate_config_file();

		// Regenerate .htaccess file.
		flush_rocket_htaccess();

		if ( $renames_cache_files ) {
			// The language has left the file name, so the files under the old names go.
			rocket_clean_domain();
		}
	}

	return $value;
}
add_filter( 'pre_update_option_polylang', 'rocket_detect_browser_language_status_change', 10, 2 );
