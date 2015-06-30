<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Conflict with WP Engine caching system
 *
 * @since 2.6.4
 */
add_action( 'init', '__rocket_stop_generate_caching_files' );
function __rocket_stop_generate_caching_files() {
	if ( class_exists( 'WpeCommon' ) ) {
		add_filter( 'do_rocket_generate_caching_files', '__return_false' );
	}
}