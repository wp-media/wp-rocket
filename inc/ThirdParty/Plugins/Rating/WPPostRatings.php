<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Rating;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with WP-PostRatings.
 */
class WPPostRatings implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether WP-PostRatings is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return rocket_has_constant( 'WP_POSTRATINGS_VERSION' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rate_post' => [ 'clear_cache_on_rate', 10, 2 ],
		];
	}

	/**
	 * Clears the post cache when a post gets rated.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_cache_on_rate( $user_id, $post_id ) {
		rocket_clean_post( $post_id );
	}
}
