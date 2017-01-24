<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( function_exists( 'EDD' ) ) :
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_edd_pages' );
	add_action( 'update_option_edd_settings', '__rocket_after_update_array_options', 10, 2 );
endif;

function rocket_exclude_edd_pages( $urls ) {
	// Easy Digital Downloads
	$edd_settings = get_option( 'edd_settings' );
	if ( isset( $edd_settings['purchase_page'] ) ) {
		$checkout_urls = get_rocket_i18n_translated_post_urls( $edd_settings['purchase_page'], 'page', '(.*)' );
		$urls = array_merge( $urls, $checkout_urls );
	}	

	return $urls;
}

add_action( 'activate_easy-digital-downloads/easy-digital-downloads.php', 'rocket_activate_edd', 11 );
function rocket_activate_edd() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_edd_pages' );

	// Update .htaccess file rules
	flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_easy-digital-downloads/easy-digital-downloads.php', 'rocket_deactivate_edd', 11 );
function rocket_deactivate_edd() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_edd_pages' );

	// Update .htaccess file rules
	flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}