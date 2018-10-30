<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( function_exists( 'it_exchange_get_page_type' ) && function_exists( 'it_exchange_get_page_url' ) ) :
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_ithemes_exchange_pages' );
	add_action( 'update_option_it-storage-exchange_settings_pages', 'rocket_after_update_single_options', 10, 2 );
endif;


/**
 * Exclude iThemes Exchange pages from cache.
 *
 * @param array $urls Array of URLs to exclude from cache.
 * @return array Updated array of URLs to exclude from cache
 */
function rocket_exclude_ithemes_exchange_pages( $urls ) {
	$pages = array(
		'purchases',
		'confirmation',
		'account',
		'profile',
		'downloads',
		'purchases',
		'log-in',
		'log-out',
	);

	foreach ( $pages as $page ) {
		if ( it_exchange_get_page_type( $page ) === 'WordPress' ) {
			$exchange_urls = get_rocket_i18n_translated_post_urls( it_exchange_get_page_wpid( $page ) );
		} else {
			$exchange_urls = array( rocket_extract_url_component( it_exchange_get_page_url( $page ), PHP_URL_PATH ) );
		}

		$urls = array_merge( $urls, $exchange_urls );
	}

	return $urls;
}


/**
 * Exclude iThemes Exchanges pages from cache on plugin activation.
 */
function rocket_activate_ithemes_exchange() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_ithemes_exchange_pages' );

	// Update .htaccess file rules.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_ithemes-exchange/ithemes-exchange.php', 'rocket_activate_ithemes_exchange', 11 );

/**
 * Remove iThemes Exchanges pages from cache exclusion on plugin deactivation.
 */
function rocket_deactivate_ithemes_exchange() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_exclude_ithemes_exchange_pages' );

	// Update .htaccess file rules.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_ithemes-exchange/ithemes-exchange.php', 'rocket_deactivate_ithemes_exchange', 11 );
