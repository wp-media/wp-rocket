<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility class for SpinUpWP
 *
 * @since 3.6.2
 */
class SpinUpWP implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.2
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! getenv( 'SPINUPWP_CACHE_PATH' ) ) {
			return [];
		}

		return [
			'do_rocket_generate_caching_files'   => 'return_false',
			'rocket_display_varnish_options_tab' => 'return_false',
			'rocket_cache_mandatory_cookies'     => 'return_empty_array',
			'after_rocket_clean_domain'          => 'purge_site',
			'wp_rocket_loaded'                   => 'remove_actions',
		];
	}

	/**
	 * Purge SpinUpWP cache after clean domain.
	 *
	 * @since 3.6.2
	 */
	public function purge_site() {
		if ( ! function_exists( 'spinupwp_purge_site' ) ) {
			return;
		}

		spinupwp_purge_site();
	}

	/**
	 * Remove rocket_clean_domain which prevents a double clear of the cache.
	 *
	 * @since 3.6.2
	 */
	public function remove_actions() {
		remove_action( 'switch_theme', 'rocket_clean_domain' );
	}

}
