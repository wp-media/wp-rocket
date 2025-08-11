<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'init' => 'on_init',
		];
	}

	/**
	 * Initialize the performance monitoring functionality.
	 *
	 * @return void
	 */
	public function on_init(): void {
		// Basic initialization - the table creation is handled by the ServiceProvider
		// This subscriber exists primarily to ensure the ServiceProvider is loaded
	}
}
