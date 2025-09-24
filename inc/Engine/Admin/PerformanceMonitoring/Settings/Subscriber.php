<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Settings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handle Add-On license status display
 *
 * @since 3.20
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Controller
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Instantiate the class
	 *
	 * @param Controller $controller Controller.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_after_account_data' => [ 'display_addon_status', 9 ], // Higher priority than RocketCDN.
		];
	}

	/**
	 * Displays the Add-On license status on the dashboard tab
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	public function display_addon_status() {
		$this->controller->display_addon_status();
	}
}
