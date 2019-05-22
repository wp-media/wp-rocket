<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'PL_INSTANCE_REF' ) && class_exists( '\Presslabs\Cache\CacheHandler' ) ) {
	if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {	
		require_once WP_CONTENT_DIR . '/advanced-cache.php';
        
	add_action( 'pl_pre_cache_refresh', 'rocket_clean_files', 0 );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	add_filter( 'do_rocket_generate_caching_files', '__return_false', PHP_INT_MAX );
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );
	add_action( 'after_rocket_clean_home', 'rocket_pl_clean_home', 10, 2 );
	add_action( 'after_rocket_clean_file', 'rocket_pl_clean_post', 2 );
    add_action( 'pl_pre_url_button_cache_refresh', 'rocket_clean_files' );
	add_action( 'wp_rocket_loaded', 'rocket_remove_partial_purge_hooks' );

	/**
	 * We clear the cache only on the post, homepage and listings when
	 * creating/updating/deleting posts.
	 *
	 * @since 3.3
	 *
	 * @param object $post The Post object itself for which the action occured.
	 * @param array  $permalink A list of permalinks to be flushed from cache.
	 *
	 * @return void
	 */
	function rocket_pl_clean_post( $post = false, $permalink = false ) {
		if ( ! $post || ! $permalink ) {
			return;
		}

		$cache_handler = new \Presslabs\Cache\CacheHandler();

		$cache_handler->invalidate_url( $permalink[0], true );
		$cache_handler->invalidate_url( home_url( '/' ), true );
		$cache_handler->purge_cache( 'listing' );
	}

	/**
	 * We clear the cache for the homepage URL when using
	 * "Purge this URL" from the admin bar on the front end
	 *
	 * @since 3.3
	 *
	 * @param string $root WP Rocket root cache path.
	 * @param string $lang Current language.
	 * @return void
	 */
	function rocket_pl_clean_home( $root = false, $lang = false ) {
		if ( ! $post || ! $permalink ) {
			return;
		}

		$cache_handler = new \Presslabs\Cache\CacheHandler();
		$cache_handler->invalidate_url( home_url( '/' ), true );
	}

	/**
	 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
	 *
	 * @since 3.3
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
		remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
	}

	if ( !defined('DISABLE_CDN_OFFLOAD') && defined( 'PL_CDN_HOST' ) ) {
		/**
		 * If we have CDN enabled we'll add our HOST to the list
		 * 
		 * @since 3.3

		 * @param Array $hosts Array of CDN hosts.
		 * @return Array Updated array of CDN hosts
		 */
		function rocket_add_pl_cdn( $hosts ) {
			$hosts[] = constant('PL_CDN_HOST');
			return $hosts;
		}
		add_filter( 'rocket_cdn_cnames', 'rocket_add_pl_cdn', 1 );
	}
  }
}
