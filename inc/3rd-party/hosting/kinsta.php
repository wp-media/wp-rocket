<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {

	add_filter( 'do_rocket_generate_caching_files', '__return_false', PHP_INT_MAX );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );

	/**
	 * Clear Kinsta cache when clearing WP Rocket cache
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	function rocket_clean_kinsta_cache() {
		global $KinstaCache;
		$KinstaCache->KinstaCachePurge->purge_complete_caches();
	}
	add_action( 'after_rocket_clean_domain', 'rocket_clean_kinsta_cache' );

	if ( Kinsta\CDNEnabler::cdn_is_enabled() ) {
		/**
		 * Add Kinsta CDN to WP Rocket CDN hosts list if enabled
		 *
		 * @since 3.0
		 * @author Remy Perona
		 *
		 * @param Array $hosts Array of CDN hosts.
		 * @return Array Updated array of CDN hosts
		 */
		function rocket_add_kinsta_cdn_cname( $hosts ) {
			$hosts[] = $_SERVER['KINSTA_CDN_DOMAIN'];

			return $hosts;
		}
		add_filter( 'rocket_cdn_cnames', 'rocket_add_kinsta_cdn_cname', 1 );
	}
}
