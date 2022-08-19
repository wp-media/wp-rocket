<?php
namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Event_Management\Subscriber_Interface;

class BeaverBuilder implements Subscriber_Interface {
	/**
	 * Events this subscriber listens to
	 *
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! rocket_get_constant( 'FL_BUILDER_VERSION' ) ) {
			return [];
		}

		return [
			'fl_builder_before_save_layout' => 'purge_cache',
			'fl_builder_cache_cleared'      => 'purge_cache',
		];
	}

	/**
	 * Purge the cache when the beaver builder layout is updated to update the minified files content & URL
	 *
	 * Previously rocket_beaver_builder_clean_domain()
	 *
	 * @since 3.6
	 * @author Remy Perona
	 */
	public function purge_cache() {
		rocket_clean_minify();
		rocket_clean_domain();
	}
}
