<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Compatibility with Easy Digital Downloads Software Licensing addon.
 *
 * @since 2.7
 */

if ( class_exists( 'EDD_Software_Licensing' ) && defined( 'EDD_SL_VERSION' ) ) :

    // Exclude EDD SL endpoint from cache on WP Rocket activation
    add_filter( 'rocket_cache_reject_uri'	 , '_rocket_exclude_edd_sl_endpoint' );
    
endif;

// Exclude EDD SL endpoint from cache when we activate the plugin
add_action( 'activate_edd-software-licensing/edd-software-licenses.php', '__rocket_activate_edd_software_licensing', 11 );
function __rocket_activate_edd_software_licensing() {
    add_filter( 'rocket_cache_reject_uri'	 , '_rocket_exclude_edd_sl_endpoint' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Remove exclusion when we deactivate the plugin
add_action( 'deactivate_edd-software-licensing/edd-software-licenses.php', '__rocket_deactivate_edd_software_licensing', 11 );
function __rocket_deactivate_edd_software_licensing() {
	remove_filter( 'rocket_cache_reject_uri'	 , '_rocket_exclude_edd_sl_endpoint' );

	// Update the WP Rocket rules on the .htaccess file
	flush_rocket_htaccess();

	// Regenerate the config file
	rocket_generate_config_file();
}

// Exclude EDD SL endpoint from caching
function _rocket_exclude_edd_sl_endpoint( $uri ) {
	$uri[] = '/edd-sl/(.*)';
	return $uri;
}