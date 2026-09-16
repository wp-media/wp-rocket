<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Rating;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with kk Star Ratings.
 */
class KKStarRatings implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether kk Star Ratings is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return class_exists( 'BhittaniPlugin_kkStarRatings' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'kksr_rate' => 'clear_cache_on_rate',
		];
	}

	/**
	 * Clears the post cache when a post gets rated.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_cache_on_rate( $post_id ) {
		rocket_clean_post( $post_id );
	}
}
