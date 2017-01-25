<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WPaaS\Plugin' ) ) :

	add_filter( 'do_rocket_generate_caching_file', '__return_false' );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	add_filter( 'set_rocket_wp_cache_define', '__return_true' );

	add_action( 'wpaas_cache_banned', 'rocket_godaddy_preload_cache' );
	function rocket_godaddy_preload_cache() {
		// Preload cache
		run_rocket_preload_cache( 'cache-preload' );
	}

endif;