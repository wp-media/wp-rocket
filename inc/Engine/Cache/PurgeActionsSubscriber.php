<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the cache purge actions
 *
 * @since 3.5
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
			'create_term'    => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
			'edit_term'      => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
			'delete_term'    => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
		];
	}

	/**
	 * Purges the cache of the corresponding user
	 *
	 * @since 3.5
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
	 * Purges the cache when a public term is created|updated|deleted
	 *
	 * @since 3.5.5
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	public function maybe_purge_cache_on_term_change( $term_id, $tt_id, $taxonomy ) {
		if ( ! $this->is_taxonomy_public( $taxonomy ) ) {
			return;
		}

		rocket_clean_domain();
	}

	/**
	 * Checks if the given taxonomy is public
	 *
	 * @param string $name Taxonomy name.
	 * @return bool
	 */
	private function is_taxonomy_public( $name ) {
		$taxonomy = get_taxonomy( $name );

		if ( false === $taxonomy ) {
			return false;
		}

		return ( $taxonomy->public && $taxonomy->publicly_queryable );
	}

	/**
	 * Checks if the user cache should be purged
	 *
	 * @since 3.5
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
