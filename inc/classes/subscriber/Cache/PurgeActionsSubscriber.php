<?php
namespace WP_Rocket\Subscriber\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the cache purge actions
 *
 * @since 3.5
 * @author Remy Perona
 */
class PurgeActionsSubscriber implements Subscriber_Interface {
	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'profile_update' => 'purge_user_cache',
			'delete_user'    => 'purge_user_cache',
		];
	}

	/**
	 * Purges the cache of the corresponding user
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function purge_user_cache( $user_id ) {
		if ( ! $this->should_purge_user_cache() ) {
			return;
		}

		rocket_clean_user( $user_id );
	}

	/**
	 * Checks if the user cache should be purged
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function should_purge_user_cache() {
		if ( ! $this->options->get( 'cache_logged_user', 0 ) ) {
			return false;
		}

		// This filter is documented in /inc/functions/files.php.
		return ! (bool) apply_filters( 'rocket_common_cache_logged_users', false );
	}
}
