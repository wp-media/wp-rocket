<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	private $render;

	public function __construct( Render $render ) {
		$this->render = $render;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_dashboard_after_account_data' => [ 'render_ui', 11 ],
		];
	}

	public function render_ui() {
		$this->render->render_ui();
	}
}
