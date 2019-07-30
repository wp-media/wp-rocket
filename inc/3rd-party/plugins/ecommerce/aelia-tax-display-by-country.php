<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Compatibility with Aelia Tax Display by Country.
 *
 * @since 2.8.15
 */
if ( class_exists( 'Aelia\WC\TaxDisplayByCountry\WC_Aelia_Tax_Display_By_Country' ) ) :
	/**
	 * Generate a caching file depending to the tax display cookie values
	 */
	add_filter( 'rocket_htaccess_mod_rewrite' , '__return_false', 68 );
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_aelia_tax_display_by_country_dynamic_cookies' );
endif;

/**
 * Add cookies when we activate the plugin
 *
 * @since 2.8.15
 * @author Remy Perona
 */
function rocket_activate_aelia_tax_display_by_country() {
	add_filter( 'rocket_htaccess_mod_rewrite' , '__return_false', 68 );
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_aelia_tax_display_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_woocommerce-tax-display-by-country/woocommerce-tax-display-by-country.php', 'rocket_activate_aelia_tax_display_by_country', 11 );

/**
 * Remove cookies when we deactivate the plugin
 *
 * @since 2.8.15
 * @author Remy Perona
 */
function rocket_deactivate_aelia_tax_display_by_country() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false', 68 );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_aelia_tax_display_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_woocommerce-tax-display-by-country/woocommerce-tax-display-by-country.php', 'rocket_deactivate_aelia_tax_display_by_country', 11 );

/**
 * Add the Aelia Tax Display by Country cookies to generate caching files depending on their values
 *
 * @since 2.8.15
 * @author Remy Perona
 *
 * @param array $cookies Cookies list to use for dynamic caching.
 * @return array Updated cookies list
 */
function rocket_add_aelia_tax_display_by_country_dynamic_cookies( $cookies ) {
	$cookies[] = 'aelia_customer_country';
	$cookies[] = 'aelia_customer_state';
	$cookies[] = 'aelia_tax_exempt';

	return $cookies;
}
