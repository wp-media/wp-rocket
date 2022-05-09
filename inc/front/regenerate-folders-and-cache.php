<?php

defined( 'ABSPATH' ) || exit;

/**
 * After restart of docker container (or deleting files anyhow) missing file needs to be recreated without need to enter admin panel.
 * This should happen right after first visit on any page.
 *
 * @since  3.11.2
 * @author Hubert Badura
 * @author Jarosław Krawczyk
 */
function wp_rocket_regenerate_cache_filesystem_and_config() {
	// If cache folders are missing, then regenerate.
	if ( ! file_exists( ABSPATH . 'wp-content/cache/wp-rocket/' ) ||
		! file_exists( ABSPATH . 'wp-content/cache/min/' ) ||
		! file_exists( ABSPATH . 'wp-content/cache/busting/' ) ||
		! file_exists( ABSPATH . 'wp-content/cache/critical-css/' ) ) {
		rocket_init_cache_dir();
	}

	// If config folder is missing, then regenerate.
	if ( ! file_exists( ABSPATH . 'wp-content/cache/wp-rocket-config/' ) ) {
		rocket_generate_config_file();
	}

	// If advanced-cache.php is missing, then regenerate.
	if ( ! file_exists( ABSPATH . 'wp-content/cache/advanced-cache.php' ) ) {
		rocket_generate_advanced_cache_file();
	}
}

add_action( 'wp_loaded', 'wp_rocket_regenerate_cache_filesystem_and_config' );
