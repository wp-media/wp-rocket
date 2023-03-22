<?php

namespace WP_Rocket\ThirdParty\Hostings;

use SiteGround_Optimizer\Supercacher\Supercacher;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Siteground extends AbstractNoCacheHost {

	use ReturnTypesTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( rocket_is_supercacher_active() ) {
			return [];
		}

		$events = [
			'admin_post_sg-cachepress-purge'     => [ 'sg_clear_cache', 0 ],
			'after_rocket_clean_domain'          => 'clean_supercacher',
			'rocket_display_varnish_options_tab' => 'return_false',
			'rocket_cache_mandatory_cookies'     => [ 'return_empty_array', PHP_INT_MAX ],
		];

		/**
		 * Force WP Rocket caching on SG Optimizer versions before 4.0.5.
		 *
		 * @since  3.0.4
		 * @author Arun Basil Lal
		 *
		 * @link   https://github.com/wp-media/wp-rocket/issues/925
		 */
		if ( version_compare( rocket_get_sg_optimizer_version(), '4.0.5' ) < 0 ) {
			$events['do_rocket_generate_caching_files'] = [ 'return_true', 11 ];
		}

		if ( version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
			$events['wp_ajax_sg-cachepress-purge'] = [ 'sg_clear_cache', 0 ];
		} else {
			$events['wp_ajax_admin_bar_purge_cache'] = [ 'sg_clear_cache', 0 ];
		}

		return $events;
	}

	/**
	 * Returns the current version of the SG Optimizer plugin.
	 *
	 * @since  3.2.3.1
	 * @author Remy Perona
	 *
	 * @return string version number.
	 */
	public function get_sg_optimizer_version() {
		static $version;

		if ( isset( $version ) ) {
			return $version;
		}

		$sg_optimizer = get_file_data( WP_PLUGIN_DIR . '/sg-cachepress/sg-cachepress.php', [ 'Version' => 'Version' ] );
		$version      = $sg_optimizer['Version'];

		return $version;
	}

	/**
	 * Checks if SG Optimizer Supercache is active.
	 *
	 * @since  3.2.3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function is_supercacher_active() {
		if ( ! version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
			global $sg_cachepress_environment;

			return isset( $sg_cachepress_environment ) && $sg_cachepress_environment instanceof SG_CachePress_Environment && $sg_cachepress_environment->cache_is_enabled();
		}

		return (bool) get_option( 'siteground_optimizer_enable_cache', 0 );
	}

	/**
	 * Call the cache server to purge the cache with SuperCacher (SiteGround).
	 *
	 * @since 2.3
	 *
	 * @return void
	 */
	public function clean_supercacher() {
		if ( ! rocket_is_supercacher_active() ) {
			return;
		}

		if ( ! version_compare( rocket_get_sg_optimizer_version(), '5.0' ) < 0 ) {
			Supercacher::purge_cache();
		} elseif ( isset( $sg_cachepress_supercacher ) && $sg_cachepress_supercacher instanceof SG_CachePress_Supercacher ) {
			$sg_cachepress_supercacher->purge_cache();
		}
	}

	/**
	 * Clean WP Rocket cache when cleaning SG cache
	 *
	 * @return void
	 */
	public function sg_clear_cache() {
		if ( empty( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'sg-cachepress-purge' ) ) {
			return;
		}

		if ( ! current_user_can( 'rocket_purge_cache' ) ) {
			return;
		}

		rocket_clean_domain();
	}
}
