<?php
declare(strict_types=1);

namespace WP_Rocket\Logger;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {
	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_before_debug_status_check' => 'maybe_delete_log_file',
		];
	}

	/**
	 * Automatically deletes the log file if it exceeds a maximum file size.
	 *
	 * @param bool $debug_enabled Whether debug is enabled and log file auto-delete should proceed.
	 * @return void
	 */
	public function maybe_delete_log_file( $debug_enabled ): void {
		Logger::maybe_delete_log_file( $debug_enabled );
	}
}
