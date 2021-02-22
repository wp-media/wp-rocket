<?php

namespace WP_Rocket\Engine\Htaccess;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Htaccess Admin Subscriber
 *
 * @since 3.8
 */
class AdminSubscriber implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.8
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'permalink_structure_changed' => 'flush_htaccess',
		];
	}

	/**
	 * Flush rocket htaccess rules when permalink changed.
	 *
	 * @since  3.8
	 *
	 * @return void
	 */
	public function flush_htaccess() {
		flush_rocket_htaccess();
	}

}
