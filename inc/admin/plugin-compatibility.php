<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * When Woocommerce, EDD & Jigoshop options are saved,
 * we update .htaccess & config file to get the right checkout page to exclude to the cache.
 *
 * @since 2.4
 */
// WooCommerce
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

// Easy Digital Downloads
add_action( 'update_option_edd_settings', 'rocket_after_save_edd_options', 10, 2 );
function rocket_after_save_edd_options( $old_value, $value ) {
	if ( ! empty( $_POST ) && $old_value['purchase_page'] != $value['purchase_page'] ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();
	}
}

// Jigoshop
add_action( 'update_option_jigoshop_options', 'rocket_after_save_jigoshop_options', 10, 2 );
function rocket_after_save_jigoshop_options( $old_value, $value ) {
	if ( ! empty( $_POST ) && ( $old_value['jigoshop_cart_page_id'] != $value['jigoshop_cart_page_id'] || $old_value['jigoshop_checkout_page_id'] != $value['jigoshop_checkout_page_id'] ) ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();
	}
}