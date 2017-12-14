<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );


/**
 * Exclude pages of Give plugin from cache.
 *
 * @since 2.7
 */
if ( defined( 'GIVE_VERSION' ) && function_exists( 'give_get_settings' ) ) {
	add_filter( 'rocket_cache_reject_uri', 'rocket_add_give_exclude_pages' );
	add_action( 'update_option_give_settings', 'rocket_after_update_single_options', 10, 2 );
}

/**
 * Add give pages to the excluded pages
 *
 * @since 2.7
 *
 * @param Array $urls Array of excluded pages.
 * @return Array Updated array of excluded pages
 */
function rocket_add_give_exclude_pages( $urls ) {
	$give_options = give_get_settings();
	$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $give_options['success_page'], 'page' ) );
	$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $give_options['history_page'], 'page' ) );
	$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $give_options['failure_page'], 'page' ) );

	return $urls;
}

/**
 * Add give pages to the excluded pages when activating the plugin
 *
 * @since 2.7
 */
function rocket_activate_give() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_add_give_exclude_pages' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_give/give.php', 'rocket_activate_give', 11 );

/**
 * Remove give pages from the excluded pages when activating the plugin
 *
 * @since 2.7
 */
function rocket_remove_give_exclude_pages() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_add_give_exclude_pages' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_give/give.php', 'rocket_remove_give_exclude_pages', 11 );
