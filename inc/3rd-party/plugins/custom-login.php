<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );


/**
 * Exclude plugin custom login page template from cache.
 *
 * @since 2.7
 *
 */
if ( class_exists( 'Custom_Login_Page_Template' ) ) {
    add_filter( 'rocket_cache_reject_uri', '__rocket_add_custom_login_exclude_pages' );
    add_action( 'update_option_custom_login_page_template', '__rocket_after_update_single_options', 10, 2 );
}

function __rocket_add_custom_login_exclude_pages( $urls ) {
    $clpt_options = get_option( 'custom_login_page_template' );
    $urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $clpt_options['login_page_id'], 'page' ) );

	return $urls;
}

add_action( 'activate_custom-login-page-template/custom-login-page-template.php', '__rocket_activate_custom_login_page_template', 11 );
function __rocket_activate_custom_login_page_template() {
    add_filter( 'rocket_cache_reject_uri', '__rocket_add_custom_login_exclude_pages' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_custom-login-page-template/custom-login-page-template.php', '__rocket_remove_custom_login_exclude_pages', 11 );
function __rocket_remove_custom_login_exclude_pages() {
    remove_filter( 'rocket_cache_reject_uri', '__rocket_add_custom_login_exclude_pages' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}