<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with WooCommerce Currency Converter Widget.
 *
 * @since 2.7
 */

if ( class_exists( 'WC_Currency_Converter' ) ) :

// Add cookie to config file when WP Rocket is activated and WooCommerce Currency Converter Widget is already active
	    add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	    add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_woocommerce_currency_converter_dynamic_cookies', 11 );
	    add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_woocommerce_currency_converter_mandatory_cookie', 11 );
	    add_action( 'update_option_woocommerce_default_customer_address', '__rocket_after_update_single_options', 10, 2 );
    
endif;

// Add cookies when we activate the plugin
add_action( 'activate_woocommerce-currency-converter-widget/currency-converter.php', '__rocket_activate_woocommerce_currency_converter', 11 );
function __rocket_activate_woocommerce_currency_converter() {
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_woocommerce_currency_converter_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_woocommerce_currency_converter_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove cookies when we deactivate the plugin
add_action( 'deactivate_woocommerce-currency-converter-widget/currency-converter.php', '__rocket_deactivate_woocommerce_currency_converter', 11 );
function __rocket_deactivate_woocommerce_currency_converter() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies', '_rocket_add_woocommerce_currency_converter_dynamic_cookies' );
	remove_filter( 'rocket_cache_mandatory_cookies', '_rocket_add_woocommerce_currency_converter_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Add the WooCommerce Currency Converter Widget cookie to generate caching files depending on its value
function _rocket_add_woocommerce_currency_converter_dynamic_cookies( $cookies ) {
	$cookies[] = 'woocommerce_current_currency';
	return $cookies;
}

// Add the WooCommerce Currency Converter Widget cookie to the list of mandatory cookies before generating the caching files
function _rocket_add_woocommerce_currency_converter_mandatory_cookie( $cookies ) {
	$widget_woocommerce_currency_converter = get_option( 'widget_woocommerce_currency_converter' );

	if ( ! empty( $widget_woocommerce_currency_converter ) && 'geolocation_ajax' === get_option( 'woocommerce_default_customer_address' ) ) {
		$cookies[] = 'woocommerce_current_currency';
	}

	return $cookies;
}