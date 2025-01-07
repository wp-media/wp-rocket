<?php

namespace WP_Rocket\ThirdParty\Hostings;

use NinukisCaching;
use WP_Post;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Pressidium extends AbstractNoCacheHost {

	use ReturnTypesTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];
		if ( defined( 'WP_NINUKIS_WP_NAME' ) ) {
			$events['rocket_varnish_field_settings']           = 'pressidium_varnish_field';
			$events['rocket_display_input_varnish_auto_purge'] = 'return_false';
			$events['rocket_cache_mandatory_cookies']          = [ 'return_empty_array', PHP_INT_MAX ];
			$events['admin_init']                              = 'clear_cache_after_pressidium';
		}

		if ( class_exists( 'NinukisCaching' ) ) {
			$events['rocket_after_clean_domain'] = 'clean_pressidium';
			$events['after_rocket_clean_file']   = [ 'purge_url', 10, 1 ];
			$events['after_rocket_clean_post']   = [ 'clean_post', 10, 2 ];
		}

		return $events;
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function pressidium_varnish_field( $settings ) {
		// Translators: %s = Hosting name.
		$settings['varnish_auto_purge']['title'] = sprintf( __( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ), 'Pressidium' );

		return $settings;
	}

	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Pressidium Hosting
	 *
	 * @since 2.5.11
	 *
	 * @return void
	 */
	public function clear_cache_after_pressidium() {
		if ( isset( $_POST['purge-all'] ) && current_user_can( 'manage_options' ) && check_admin_referer( WP_NINUKIS_WP_NAME . '-caching' ) ) {
			// Clear all caching files.
			rocket_clean_domain();
		}
	}

	/**
	 * Call the cache server to purge the cache with Pressidium hosting.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public function clean_pressidium() {
		$plugin = NinukisCaching::get_instance();
		$plugin->purgeAllCaches();
	}

	/**
	 * Returns the path of URLs.
	 *
	 * @param array|string $urls Urls we want to get paths.
	 * @return array the path.
	 */
	private function get_paths( $urls ) {
		if ( ! is_array( $urls ) ) {
			$urls = (array) $urls;
		}

		$paths = [];

		foreach ( $urls as $url ) {
			$parsed_url = get_rocket_parse_url( $url );
			$paths[]    = $parsed_url['path'];

		}
		return $paths;
	}

	/**
	 * Purge the cache of Pressidium from paths.
	 *
	 * @param array $paths Paths of pages we are going to purge cache.
	 *
	 * @return void
	 */
	private function purge_cache( $paths ) {
		NinukisCaching::get_instance()->purge_cache( $paths );
	}

	/**
	 * Purge the cache for the given URL.
	 *
	 * @param string|array $url URL we want to purge.
	 *
	 * @return void
	 */
	public function purge_url( $url ) {
		$paths = $this->get_paths( $url );
		$this->purge_cache( $paths );
	}

	/**
	 * Clean cache of post.
	 *
	 * @param WP_Post $post Post that need to be cleaned.
	 * @param array   $purge_urls URLs that need to be purged.
	 *
	 * @return void
	 */
	public function clean_post( $post, $purge_urls ) {
		// Purge related urls.
		$paths = $this->get_paths( $purge_urls );
		$this->purge_cache( $paths );
	}
}
