<?php

namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;

/**
 * Subscriber for the cache admin events
 *
 * @since 3.5.5
 */
class AdminSubscriber implements Event_Manager_Aware_Subscriber_Interface {
	/**
	 * Event Manager instance
	 *
	 * @var Event_Manager;
	 */
	protected $event_manager;

	/**
	 * AdvancedCache instance
	 *
	 * @var AdvancedCache
	 */
	private $advanced_cache;

	/**
	 * WPCache instance
	 *
	 * @var WPCache
	 */
	private $wp_cache;

	/**
	 * Instantiate the class
	 *
	 * @param AdvancedCache $advanced_cache AdvancedCache instance.
	 * @param WPCache       $wp_cache       WPCache instance.
	 */
	public function __construct( AdvancedCache $advanced_cache, WPCache $wp_cache ) {
		$this->advanced_cache = $advanced_cache;
		$this->wp_cache       = $wp_cache;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG' );

		return [
			'admin_init'            => [
				[ 'register_terms_row_action' ],
				[ 'maybe_set_wp_cache' ],
			],
			'admin_notices'         => [
				[ 'notice_advanced_cache_permissions' ],
				[ 'notice_wp_config_permissions' ],
			],
			"update_option_{$slug}" => [ 'maybe_set_wp_cache', 12 ],
			'site_status_tests'     => 'add_wp_cache_status_test',
		];
	}

	/**
	 * Sets the event manager for the subscriber.
	 *
	 * @param Event_Manager $event_manager Event Manager instance.
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * Registers the action for each public taxonomy
	 *
	 * @since 3.5.5
	 *
	 * @return void
	 */
	public function register_terms_row_action() {
		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		foreach ( $taxonomies as $taxonomy ) {
			$this->event_manager->add_callback( "{$taxonomy}_row_actions", [ $this, 'add_purge_term_link' ], 10, 2 );
		}
	}

	/**
	 * Adds a link "Purge this cache" in the terms list table
	 *
	 * @param array   $actions An array of action links to be displayed.
	 * @param WP_Term $term Term object.
	 *
	 * @return array
	 */
	public function add_purge_term_link( $actions, $term ) {
		if ( ! current_user_can( 'rocket_purge_terms' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( "admin-post.php?action=purge_cache&type=term-{$term->term_id}&taxonomy={$term->taxonomy}" ),
			"purge_cache_term-{$term->term_id}"
		);

		$actions['rocket_purge'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$url,
			__( 'Clear this cache', 'rocket' )
		);

		return $actions;
	}

	/**
	 * Displays the notice for advanced-cache.php permissions
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function notice_advanced_cache_permissions() {
		$this->advanced_cache->notice_permissions();
	}

	/**
	 * Set WP_CACHE constant to true if needed
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function maybe_set_wp_cache() {
		$this->wp_cache->maybe_set_wp_cache();
	}

	/**
	 * Displays the notice for wp-config.php permissions
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function notice_wp_config_permissions() {
		$this->wp_cache->notice_wp_config_permissions();
	}

	/**
	 * Adds a Site Health check for the WP_CACHE constant value
	 *
	 * @since 3.6.1
	 *
	 * @param array $tests An array of tests to perform.
	 * @return array
	 */
	public function add_wp_cache_status_test( $tests ) {
		return $this->wp_cache->add_wp_cache_status_test( $tests );
	}
}
