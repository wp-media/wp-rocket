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
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_init' => 'register_terms_row_action',
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
}
