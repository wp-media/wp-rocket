<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'WP_NINUKIS_WP_NAME' ) ) :
	add_action( 'admin_init', '__rocket_clear_cache_after_pressidium' );
	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Pressidium Hosting
	 *
	 * @since 2.5.11
	 *
	 * @return void
	 */
	function __rocket_clear_cache_after_pressidium() {
		if ( isset( $_POST['purge-all'] ) && current_user_can( 'manage_options' ) && check_admin_referer( WP_NINUKIS_WP_NAME . '-caching' ) ) {
			// Clear all caching files
			rocket_clean_domain();
			
			// Preload cache
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
endif;

if ( class_exists( 'Ninukis_Plugin' ) ) :
	add_action( 'after_rocket_clean_domain', 'rocket_clean_pressidium' );
	/**
	 * Call the cache server to purge the cache with Pressidium hosting.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	function rocket_clean_pressidium() {	
		$plugin = Ninukis_Plugin::get_instance();
		$plugin->purgeAllCaches();		
	}
endif;