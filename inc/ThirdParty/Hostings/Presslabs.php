<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Presslabs hosting compatibility.
 *
 * @since 3.24
 */
class Presslabs implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Presslabs constructor.
	 *
	 * Kept from the legacy presslabs.php: loads the Presslabs advanced-cache drop-in so the
	 * \Presslabs\Cache\CacheHandler used by the purge callbacks is available. This is idempotent
	 * (require_once) and safe in every context — including the activation/deactivation path, where
	 * HostResolver now instantiates this subscriber: the host is only detected when
	 * class_exists( '\Presslabs\Cache\CacheHandler' ) is already true, so the class is loaded and this
	 * require is a no-op. See #8768.
	 */
	public function __construct() {
		require_once WP_CONTENT_DIR . '/advanced-cache.php';
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'pl_pre_cache_refresh'               => [ 'clean_files', 0 ],
			'rocket_display_varnish_options_tab' => 'return_false',
			'do_rocket_generate_caching_files'   => [ 'return_false', PHP_INT_MAX ],
			'rocket_cache_mandatory_cookies'     => [ 'return_empty_array', PHP_INT_MAX ],
			'after_rocket_clean_home'            => [ 'clean_home', 10, 2 ],
			'after_rocket_clean_file'            => [ 'clean_post', 2 ],
			'pl_pre_url_button_cache_refresh'    => 'clean_files',
			'wp_rocket_loaded'                   => 'remove_partial_purge_hooks',
		];

		if ( ! defined( 'DISABLE_CDN_OFFLOAD' ) && defined( 'PL_CDN_HOST' ) ) {
			$events['rocket_cdn_cnames'] = [ 'add_pl_cdn', 1 ];
		}

		return $events;
	}

	/**
	 * We clear the cache only on the post, homepage and listings when creating/updating/deleting posts.
	 *
	 * @since 3.3
	 *
	 * @param object|false $post The Post object itself for which the action occurred.
	 * @param array|false  $permalink A list of permalinks to be flushed from cache.
	 *
	 * @return void
	 */
	public function clean_post( $post = false, $permalink = false ) {
		if ( ! $post || ! $permalink ) {
			return;
		}

		$cache_handler = new \Presslabs\Cache\CacheHandler();

		$cache_handler->invalidate_url( $permalink[0], true );
		$cache_handler->invalidate_url( home_url( '/' ), true );
		$cache_handler->purge_cache( 'listing' );
	}

	/**
	 * We clear the cache for the homepage URL when using "Purge this URL" from the admin bar on the front end.
	 *
	 * @since 3.3
	 *
	 * @param string|false $root WP Rocket root cache path.
	 * @param string|false $lang Current language.
	 *
	 * @return void
	 */
	public function clean_home( $root = false, $lang = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
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
	public function remove_partial_purge_hooks() {
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
			function ( $hook ) {
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

	/**
	 * Clears WP Rocket cache files.
	 *
	 * @since 3.3
	 *
	 * @param mixed $urls URLs to clean, as passed by the firing hook.
	 *
	 * @return void
	 */
	public function clean_files( $urls = null ) {
		rocket_clean_files( $urls );
	}
}
