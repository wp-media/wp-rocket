<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'WpAppKit' ) ) :
	add_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );
endif;

/**
 * Add WP Appkit path to cache exclusion
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param array $urls URLs to exclude from cache.
 * @return array Updated URLs array
 */
function rocket_add_appkit_exclude_pages( $urls ) {
	$urls[] = '/wp-appkit-api/(.*)';

	return $urls;
}

/**
 * Add WP Appkit path to cache exclusion when activating the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_activate_wp_appkit() {
	add_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'activate_wp-appkit/wp-appkit.php', 'rocket_activate_wp_appkit', 11 );

/**
 * Remove Appkit path from cache exclusion when deactivating the plugin
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_deactivate_wp_appkit() {
	remove_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );

	// Update the WP Rocket rules on the .htaccess.
	flush_rocket_htaccess();

	// Regenerate the config file.
	rocket_generate_config_file();
}
add_action( 'deactivate_wp-appkit/wp-appkit.php', 'rocket_deactivate_wp_appkit', 11 );
