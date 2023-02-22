<?php

namespace WP_Rocket\ThirdParty\Hostings;

use Ninukis_Plugin;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Pressidium extends AbstractNoCacheHost
{
	use ReturnTypesTrait;

	public static function get_subscribed_events()
	{
		$events = [];
		if ( defined( 'WP_NINUKIS_WP_NAME' ) ) {
			$events['rocket_varnish_field_settings'] = 'pressidium_varnish_field';
			$events['rocket_display_input_varnish_auto_purge'] = 'return_false';
			$events['rocket_cache_mandatory_cookies'] = ['return_empty_array', PHP_INT_MAX];
			$events['admin_init'] = 'clear_cache_after_pressidium';
		}

		if ( class_exists( 'Ninukis_Plugin' ) ) {
			$events['after_rocket_clean_domain'] = 'clean_pressidium';
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

			run_rocket_sitemap_preload();
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
		$plugin = Ninukis_Plugin::get_instance();
		$plugin->purgeAllCaches();
	}
}
