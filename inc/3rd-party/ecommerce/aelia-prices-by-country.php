<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Compatibility with Aelia Prices by Country.
 *
 * @since 2.8.15
 */
if ( class_exists( 'Aelia\WC\PricesByCountry\WC_Aelia_Prices_By_Country' ) ) :
	/**
	 * Generate a caching file depending on the country cookie value
	 */
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , 'rocket_add_aelia_prices_by_country_dynamic_cookies' );
endif;

/**
 * Add cookie when we activate the plugin
 *
 * @since 2.8.15
 * @author Remy Perona
 */
function rocket_activate_aelia_prices_by_country() {
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , 'rocket_add_aelia_prices_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_woocommerce-prices-by-country/woocommerce-prices-by-country.php', 'rocket_activate_aelia_prices_by_country', 11 );

/**
 * Remove cookies when we deactivate the plugin
 *
 * @since 2.8.15
 * @author Remy Perona
 */
function rocket_deactivate_aelia_prices_by_country() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_add_aelia_prices_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_woocommerce-prices-by-country/woocommerce-prices-by-country.php', 'rocket_deactivate_aelia_prices_by_country', 11 );

/**
 * Add the Aelia Prices by Country cookie to generate caching files depending on its value
 *
 * @since 2.8.15
 * @author Remy Perona
 *
 * @param array $cookies Cookies list to use for dynamic caching.
 * @return array Updated cookies list
 */
function rocket_add_aelia_prices_by_country_dynamic_cookies( $cookies ) {
	$cookies[] = 'aelia_customer_country';
	return $cookies;
}
