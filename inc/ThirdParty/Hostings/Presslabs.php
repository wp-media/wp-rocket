<?php

namespace WP_Rocket\ThirdParty\Hostings;

use Presslabs\Cache\CacheHandler;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Presslabs extends AbstractNoCacheHost
{
	use ReturnTypesTrait;

	public static function get_subscribed_events()
	{
		require_once WP_CONTENT_DIR . '/advanced-cache.php';

		$events = [
			'pl_pre_cache_refresh' => 'rocket_clean_files',
			'rocket_display_varnish_options_tab' => 'return_false',
			'do_rocket_generate_caching_files' => 'return_false',
			'rocket_cache_mandatory_cookies' => 'return_empty_array',
			'after_rocket_clean_home' => 'pl_clean_home',
			'after_rocket_clean_file' => 'pl_clean_post',
			'pl_pre_url_button_cache_refresh' => 'rocket_clean_files',
			'wp_rocket_loaded' => 'pl_remove_partial_purge_hooks',
		];

		if ( ! defined( 'DISABLE_CDN_OFFLOAD' ) && defined( 'PL_CDN_HOST' ) ) {
			$events['rocket_cdn_cnames'] = 'add_pl_cdn';
		}

		return $events;
	}

	/**
	 * We clear the cache only on the post, homepage and listings when creating/updating/deleting posts.
	 *
	 * @since 3.3
	 *
	 * @param object $post The Post object itself for which the action occured.
	 * @param array  $permalink A list of permalinks to be flushed from cache.
	 *
	 * @return void
	 */
	function pl_clean_post( $post = false, $permalink = false ) {
		if ( ! $post || ! $permalink ) {
			return;
		}

		$cache_handler = new CacheHandler();

		$cache_handler->invalidate_url( $permalink[0], true );
		$cache_handler->invalidate_url( home_url( '/' ), true );
		$cache_handler->purge_cache( 'listing' );
	}

	/**
	 * We clear the cache for the homepage URL when using "Purge this URL" from the admin bar on the front end.
	 *
	 * @since 3.3
	 *
	 * @param string $root WP Rocket root cache path.
	 * @param string $lang Current language.
	 *
	 * @return void
	 */
	public function pl_clean_home( $root = false, $lang = false ) {
		if ( ! $post || ! $permalink ) {
			return;
		}

		$cache_handler = new CacheHandler();
		$cache_handler->invalidate_url( home_url( '/' ), true );
	}

	/**
	 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function pl_remove_partial_purge_hooks() {
		// WP core action hooks rocket_clean_post() gets hooked into.
		$clean_post_hooks = [
			// Disables the refreshing of partial cache when content is edited.
			'wp_trash_post',
			'delete_post',
			'clean_post_cache',
			'wp_update_comment_count',
		];
		// Remove rocket_clean_post() from core action hooks.
		array_map(
			function( $hook ) {
				remove_action( $hook, 'rocket_clean_post' );
			},
			$clean_post_hooks
		);
		remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
	}

	/**
	 * If we have CDN enabled we'll add our HOST to the list.
	 *
	 * @since 3.3
	 *
	 * @param array $hosts Array of CDN hosts.
	 *
	 * @return array Updated array of CDN hosts
	 */
	public function add_pl_cdn( $hosts ) {
		$hosts[] = constant( 'PL_CDN_HOST' );
		return $hosts;
	}
}
