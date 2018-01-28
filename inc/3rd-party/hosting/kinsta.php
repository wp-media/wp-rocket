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

	/**
	 * Partially clear Kinsta cache when partially clearing WP Rocket cache
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param object $post Post object.
	 * @return void
	 */
	function rocket_clean_kinsta_post_cache( $post ) {
		global $KinstaCache;
		$KinstaCache->KinstaCachePurge->initiate_purge( $post->ID, 'post' );
	}
	add_action( 'after_rocket_clean_post', 'rocket_clean_kinsta_post_cache', 10, 1 );

	/**
	 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	function rocket_remove_partial_purge_hooks() {
		// WP core action hooks rocket_clean_post() gets hooked into.
		$clean_post_hooks = array(
			// Disables the refreshing of partial cache when content is edited.
			'wp_trash_post',
			'delete_post',
			'clean_post_cache',
			'wp_update_comment_count',
		);

		// Remove rocket_clean_post() from core action hooks.
		array_map(
			function( $hook ) {
				remove_action( $hook, 'rocket_clean_post' );
			},
			$clean_post_hooks
		);
	}
	add_action( 'rocket_loaded', 'rocket_remove_partial_purge_hooks' );

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
