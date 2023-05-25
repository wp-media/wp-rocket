<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

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
	 * Purge instance
	 *
	 * @var Purge
	 */
	private $purge;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 * @param Purge        $purge   Purge instance.
	 */
	public function __construct( Options_Data $options, Purge $purge ) {
		$this->options = $options;
		$this->purge   = $purge;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG' );

		return [
			'profile_update'                      => 'purge_user_cache',
			'delete_user'                         => 'purge_user_cache',
			'create_term'                         => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
			'edit_term'                           => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
			'delete_term'                         => [ 'maybe_purge_cache_on_term_change', 10, 3 ],
			'after_rocket_clean_post'             => [
				[ 'purge_dates_archives' ],
				[ 'purge_post_terms_urls' ],
			],
			'rocket_rucss_complete_job_status'    => [ 'purge_url_cache', 100 ],
			'rocket_rucss_after_clearing_usedcss' => 'purge_url_cache',
			'rocket_after_save_dynamic_lists'     => 'purge_cache',
			'update_option_' . $slug              => [ 'purge_cache_reject_uri_partially', 10, 2 ],
			'update_option_blog_public'           => 'purge_cache',
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
	 * Purges cache for the dates archives of a post after cleaning the post
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function purge_dates_archives( $post ) {
		$this->purge->purge_dates_archives( $post );
	}

	/**
	 * Purge all terms archives urls associated to a specific post.
	 *
	 * @param  WP_Post $post Post object.
	 * @return void
	 */
	public function purge_post_terms_urls( $post ) {
		$this->purge->purge_post_terms_urls( $post );
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

	/**
	 * Purge cache after RUCSS
	 *
	 * @param string $url URL to be purged.
	 *
	 * @return void
	 */
	public function purge_url_cache( string $url ) {
		// Flush cache for this url.
		Logger::debug( 'RUCSS: Purge the cache for url: ' . $url );

		$this->purge->purge_url( $url );
	}

	/**
	 * Clean the whole cache
	 *
	 * @return void
	 */
	public function purge_cache() {
		rocket_clean_domain();
	}

	/**
	 * Purge single cache file(s) added in the Never Cache URL(s).
	 *
	 * @param array $old_value An array of previous values for the settings.
	 * @param array $value An array of submitted values for the settings.
	 * @return void
	 */
	public function purge_cache_reject_uri_partially( array $old_value, array $value ): void {
		$this->purge->purge_cache_reject_uri_partially( $old_value, $value );
	}
}
