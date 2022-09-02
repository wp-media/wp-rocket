<?php

namespace WP_Rocket\Engine\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class NoticesSubscriber implements Subscriber_Interface {

	/**
	 * Notices instance.
	 *
	 * @var Notices
	 */
	private $notices;

	/**
	 * Constructor
	 *
	 * @param Notices $notices Notices instance.
	 */
	public function __construct( Notices $notices ) {
		$this->notices = $notices;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => 'maybe_display_as_missed_tables_notice',
		];
	}

	/**
	 * Dispaly notice for missing or incorrect action scheduler tables.
	 *
	 * @since 3.12.1
	 *
	 * @return void
	 */
	public function maybe_display_as_missed_tables_notice() {
		$this->notices->maybe_display_as_missed_tables_notice();
	}
}
