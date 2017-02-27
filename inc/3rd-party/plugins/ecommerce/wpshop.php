<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'WPSHOP_VERSION' ) && class_exists( 'wpshop_tools' ) && method_exists( 'wpshop_tools','get_page_id' ) ) :
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_wpshop_pages' );
	add_action( 'update_option_wpshop_cart_page_id'			, '__rocket_after_update_single_options', 10, 2 );
	add_action( 'update_option_wpshop_checkout_page_id'		, '__rocket_after_update_single_options', 10, 2 );
	add_action( 'update_option_wpshop_payment_return_page_id', '__rocket_after_update_single_options', 10, 2 );
	add_action( 'update_option_wpshop_payment_return_nok_page_id', '__rocket_after_update_single_options', 10, 2 );
	add_action( 'update_option_wpshop_myaccount_page_id'	, '__rocket_after_update_single_options', 10, 2 );
endif;

function rocket_exclude_wpshop_pages( $urls ) {
	$pages = array(
		'wpshop_cart_page_id',
		'wpshop_checkout_page_id',
		'wpshop_payment_return_page_id',
		'wpshop_payment_return_nok_page_id',
		'wpshop_myaccount_page_id'
	);
	
	foreach( $pages as $page ) {
		if ( $page_id = wpshop_tools::get_page_id( get_option( $page ) ) ) {
			$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $page_id ) );
		}
	}

	return $urls;
}

add_action( 'activate_wpshop/wpshop.php', 'rocket_activate_wpshop', 11 );
function rocket_activate_wpshop() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_wpshop_pages' );

	// Update .htaccess file rules
	flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_wpshop/wpshop.php', 'rocket_deactivate_wpshop', 11 );
function rocket_deactivate_edd() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_wpshop_pages' );

	// Update .htaccess file rules
	flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}
