<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * When Woocommerce or EDD options are saved,
 * we update .htaccess & config file to get the right checkout page to exclude to the cache.
 *
 * @since 2.4
 */
add_action( 'update_option_woocommerce_cart_page_id', 'rocket_after_save_wc_options', 10, 2 );
add_action( 'update_option_woocommerce_checkout_page_id', 'rocket_after_save_wc_options', 10, 2 );
function rocket_after_save_wc_options( $old_value, $value ) {
	if ( ! empty( $_POST ) && $old_value != $value ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();
	}
}

add_action( 'update_option_edd_settings', 'rocket_after_save_edd_options', 10, 2 );
function rocket_after_save_edd_options( $old_value, $value ) {
	if ( ! empty( $_POST ) && $old_value['purchase_page'] != $value['purchase_page'] ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();
	}
}