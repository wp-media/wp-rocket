<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'SFML_VERSION' ) ):
	add_filter( 'rocket_cache_reject_uri', 'rocket_add_sfml_exclude_pages' );
	add_action( 'update_option_sfml', '__rocket_after_update_single_options', 10, 2 );
endif;

/**
 * Exclude SF Move Login custom urls from caching
 *
 * @since 2.9.3 Moved to 3rd party file and improved
 * @since 2.6
 *
 * @param array $urls An array of URLs to exclude from cache.
 * @return array Updated array of URLs
 */
function rocket_add_sfml_exclude_pages( $urls ) {
	if ( ! function_exists( 'sfml_get_slugs' ) ) {
		if ( file_exists( SFML_PLUGIN_DIR . 'inc/utilities.php' ) ) {
			include( SFML_PLUGIN_DIR . 'inc/utilities.php' );
		} else {
			return $urls;
		}
	}

	if ( ! class_exists( 'SFML_Options' ) && ! defined( 'SFML_NOOP_VERSION' ) ) {
		if ( file_exists( SFML_PLUGIN_DIR . 'inc/class-sfml-options.php' ) ) {
			include( SFML_PLUGIN_DIR . 'inc/class-sfml-options.php' );
		} else {
			return $urls;
		}
	}

	$sfml_slugs = sfml_get_slugs();
	$sfml_slugs = array_map( 'home_url', $sfml_slugs );
	$sfml_slugs = array_map( 'trailingslashit', $sfml_slugs );
	$sfml_slugs = array_map( 'rocket_clean_exclude_file', $sfml_slugs );
	
	foreach( $sfml_slugs as $key => $slug ) {
		$sfml_slugs[ $key ] = $slug . '?';
	}
	
	return array_merge( $urls, $sfml_slugs );
}

add_action( 'activate_sf-move-login/sf-move-login.php', 'rocket_activate_sfml', 11 );
/**
 * Add SFML custom urls to caching exclusion when activating the plugin
 *
 * @since 2.9.3
 */
function rocket_activate_sfml() {
	if ( defined( 'SFML_VERSION' ) ) {
		add_filter( 'rocket_cache_reject_uri', 'rocket_add_sfml_exclude_pages' );
		
    	// Update the WP Rocket rules on the .htaccess
    	flush_rocket_htaccess();
		
    	// Regenerate the config file
    	rocket_generate_config_file();
    }
}

add_action( 'deactivate_sf-move-login/sf-move-login.php', 'rocket_deactivate_sfml', 11 );
/**
 * Remove SFML custom urls from caching exclusion when deactivating the plugin
 *
 * @since 2.9.3
 */
function rocket_deactivate_sfml() {
	if ( defined( 'SFML_VERSION' ) ) {
		remove_filter( 'rocket_cache_reject_uri', 'rocket_add_sfml_exclude_pages' );
		
    	// Update the WP Rocket rules on the .htaccess
    	flush_rocket_htaccess();
		
    	// Regenerate the config file
    	rocket_generate_config_file();
    }
}
