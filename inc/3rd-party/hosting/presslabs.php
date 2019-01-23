<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'PL_INSTANCE_REF' ) && class_exists( '\Presslabs\Cache\CacheHandler' ) ) {
	if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
		require_once WP_CONTENT_DIR . '/advanced-cache.php';
	}

	add_action( 'pl_pre_cache_refresh', 'rocket_clean_post', 0 );
	add_action( 'after_rocket_clean_domain', 'rocket_presslabs_clear_cache' );
	add_action( 'after_rocket_clean_minify', 'rocket_presslabs_clear_cache' );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	add_action( 'after_rocket_clean_post', 'rocket_presslabs_clean_post', 2 );

	/**
	 * We clear the entire cache on the Presslabs nodes with the
	 * exception of images on the CDN.
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	function rocket_presslabs_clear_cache() {
		$cache_handler = new \Presslabs\Cache\CacheHandler();

		$cache_handler->purge_cache( 'pages' );
		$cache_handler->purge_cache( 'listing' );
		$cache_handler->purge_cache( 'assets' );
	}

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
	function rocket_presslabs_clean_post( $post, $permalink ) {
		$cache_handler = new \Presslabs\Cache\CacheHandler();

		$cache_handler->invalidate_url( $permalink[0], true );
		$cache_handler->invalidate_url( home_url( '/' ), true );
		$cache_handler->purge_cache( 'listing' );
	}
}
