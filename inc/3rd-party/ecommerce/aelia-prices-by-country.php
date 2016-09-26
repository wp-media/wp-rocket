<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with Aelia Prices by Country.
 *
 * @since 2.8.15
 */
if ( class_exists( 'Aelia\WC\PricesByCountry\WC_Aelia_Prices_By_Country' ) ) :
	/*
	 * Generate a caching file depending to the country cookie value
	 *
	 */
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_aelia_prices_by_country_dynamic_cookies' );
endif;

// Add cookie when we activate the plugin
add_action( 'activate_woocommerce-prices-by-country/woocommerce-prices-by-country.php', '__rocket_activate_aelia_prices_by_country', 11 );
function __rocket_activate_aelia_prices_by_country() {
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_aelia_prices_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove cookies when we deactivate the plugin
add_action( 'deactivate_woocommerce-prices-by-country/woocommerce-prices-by-country.php', '__rocket_deactivate_aelia_prices_by_country', 11 );
function __rocket_deactivate_aelia_prices_by_country() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies', '_rocket_add_aelia_prices_by_country_dynamic_cookies' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Add the Aelia Prices by Country cookie to generate caching files depending on its value
function _rocket_add_aelia_prices_by_country_dynamic_cookies( $cookies ) {
	$cookies[] = 'aelia_customer_country';
	return $cookies;
}