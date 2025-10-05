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
			'rocket_dashboard_after_account_data'          => [ 'display_addon_status', 9 ], // Higher priority than RocketCDN.
			'rocket_insights_settings_enabled'             => 'disable_for_free_plan',
			'pre_get_rocket_option_performance_monitoring' => 'disable_performance_monitoring_schedule',
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

	/**
	 * Disable rocket insights settings for free plan.
	 *
	 * @param bool $enabled Current status.
	 * @return bool
	 */
	public function disable_for_free_plan( $enabled ) {
		if ( ! $enabled ) {
			return $enabled;
		}

		return ! $this->controller->is_free_plan();
	}

	/**
	 * Disable performance monitoring schedule option for free users
	 *
	 * @param mixed $option_value Option value.
	 *
	 * @return int
	 */
	public function disable_performance_monitoring_schedule( $option_value ) {
		if ( ! $this->controller->is_free_plan() ) {
			return $option_value;
		}
		return 0;
	}
}
