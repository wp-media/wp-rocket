<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * When Woocommerce, EDD, iThemes Exchange, Jigoshop & WP-Shop options are saved or deleted,
 * we update .htaccess & config file to get the right checkout page to exclude to the cache.
 *
 * @since 2.4
 */
add_action( 'update_option_woocommerce_cart_page_id'     	 , '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_woocommerce_checkout_page_id' 	 , '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_wpshop_cart_page_id'			 	 , '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_wpshop_checkout_page_id'		 	 , '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_wpshop_payment_return_page_id'	 , '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_wpshop_payment_return_nok_page_id', '__rocket_after_update_wc_options', 10, 2 );
add_action( 'update_option_it-storage-exchange_settings_pages', '__rocket_after_update_wc_options', 10, 2 );
function __rocket_after_update_wc_options( $old_value, $value ) {
	if ( $old_value != $value ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();	
	}
}

add_action( 'update_option_edd_settings'	, '__rocket_after_update_edd_options', 10, 2 );
add_action( 'update_option_jigoshop_options', '__rocket_after_update_edd_options', 10, 2 );
function __rocket_after_update_edd_options( $old_value, $value ) {		
	if ( ( $old_value['purchase_page'] != $value['purchase_page'] ) || $old_value['jigoshop_cart_page_id'] != $value['jigoshop_cart_page_id'] || $old_value['jigoshop_checkout_page_id'] != $value['jigoshop_checkout_page_id'] ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();	
	}
}

/**
 * Compatibility with an usual NGINX configuration which include 
 * try_files $uri $uri/ /index.php?q=$uri&$args
 *
 * @since 2.3.9
 */
add_filter( 'rocket_cache_query_strings', '__rocket_better_nginx_compatibility' );
function __rocket_better_nginx_compatibility( $query_strings ) {
	global $is_nginx;
	
	if ( $is_nginx ) {
		$query_strings[] = 'q';
	}
	
	return $query_strings;
}