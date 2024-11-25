<?php

namespace WP_Rocket\Engine\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class ActionSchedulerSubscriber implements Subscriber_Interface {

	use ReturnTypesTrait;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'action_scheduler_check_pastdue_actions' => 'return_false',
			'test' => ['test'],
			'test2' => ['test', 10],
			'test3' => ['test', 10, 3],
			'action_scheduler_extra_action_counts'   => [
				['hide_pastdue_status_filter'],
				['hide_pastdue_status_filter', 10, 3],
			],
		];
	}

	/**
	 * Hide past-due from status filter in Action Scheduler tools page.
	 *
	 * @param array $extra_actions Array with format action_count_identifier => action count.
	 *
	 * @return array
	 */
	public function hide_pastdue_status_filter( array $extra_actions ) {
		if ( ! isset( $extra_actions['past-due'] ) ) {
			return $extra_actions;
		}

		unset( $extra_actions['past-due'] );
		return $extra_actions;
	}
}
