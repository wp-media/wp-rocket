<?php

namespace WP_Rocket\ThirdParty\Hostings;

/**
 * Subscriber for compatibility with WordPress.com hosting.
 *
 * @since 3.6.3
 */
class WordPressCom extends AbstractNoCacheHost {
	/**
	 * Array of events this subscriber listens to.
	 *
	 * @since 3.6.3
	 *
	 * @return array The array of subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files'    => 'return_false',
			'rocket_cache_mandatory_cookies'      => 'return_empty_array',
			'rocket_display_varnish_options_tab'  => 'return_false',
			'rocket_set_wp_cache_constant'        => 'return_false',
			'rocket_generate_advanced_cache_file' => 'return_false',
			'rocket_after_clean_domain'           => 'purge_wpcom_cache',
		];
	}

	/**
	 * Purge WordPress.com cache
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function purge_wpcom_cache() {
		wp_cache_flush();
	}
}
