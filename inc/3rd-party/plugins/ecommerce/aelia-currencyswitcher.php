<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with Aelia Currency Switcher.
 *
 * @since 2.7
 */
if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) :
	/*
	 * Update .htaccess & config files when the Geolocation option is updated
	 *
	 */
	add_action( 'update_option_wc_aelia_currency_switcher', '__rocket_after_update_aelia_currencyswitcher_options', 10, 2 );
	function __rocket_after_update_aelia_currencyswitcher_options( $old_value, $value ) {
		if ( ( ! isset( $old_value['ipgeolocation_enabled'] ) && isset( $value['ipgeolocation_enabled'] ) ) || ( isset( $old_value['ipgeolocation_enabled'], $value['ipgeolocation_enabled'] ) && $old_value['ipgeolocation_enabled'] != $value['ipgeolocation_enabled'] )
		) {
			// Update the WP Rocket rules on the .htaccess file
			flush_rocket_htaccess();

			// Update the config file
			rocket_generate_config_file();
		}
	}

	/*
	 * Generate a caching file depending to the currency cookie value
	 *
	 */
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_aelia_currencyswitcher_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_aelia_currencyswitcher_mandatory_cookie' );
endif;

// Add cookies when we activate the plugin
add_action( 'activate_woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php', '__rocket_activate_aelia_currencyswitcher', 11 );
function __rocket_activate_aelia_currencyswitcher() {
	add_filter( 'rocket_htaccess_mod_rewrite'	 , '__return_false' );
	add_filter( 'rocket_cache_dynamic_cookies'	 , '_rocket_add_aelia_currencyswitcher_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies' , '_rocket_add_aelia_currencyswitcher_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove cookies when we deactivate the plugin
add_action( 'deactivate_woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php', '__rocket_deactivate_aelia_currencyswitcher', 11 );
function __rocket_deactivate_aelia_currencyswitcher() {
	remove_filter( 'rocket_htaccess_mod_rewrite' , '__return_false' );
	remove_filter( 'rocket_cache_dynamic_cookies', '_rocket_add_aelia_currencyswitcher_dynamic_cookies' );
	remove_filter( 'rocket_cache_mandatory_cookies', '_rocket_add_aelia_currencyswitcher_mandatory_cookie' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Add the Aelia Currency Switcher cookies to generate caching files depending to their values
function _rocket_add_aelia_currencyswitcher_dynamic_cookies( $cookies ) {
	$cookies[] = 'aelia_cs_recalculate_cart_totals';
	$cookies[] = 'aelia_cs_selected_currency';
	$cookies[] = 'aelia_customer_country';
	return $cookies;
}

// Add the Aelia Currency Switcher to the list of mandatory cookies before to generate caching files
function _rocket_add_aelia_currencyswitcher_mandatory_cookie( $cookies ) {
	$acs_options = get_option( 'wc_aelia_currency_switcher' );

	if ( ! empty( $acs_options['ipgeolocation_enabled'] ) ) {
		$cookies[] = 'aelia_cs_selected_currency';
		$cookies[] = 'aelia_customer_country';
	}

	return $cookies;
}