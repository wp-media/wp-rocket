<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

use WP_Rocket\Event_Management\Subscriber_Interface;


class Subscriber implements Subscriber_Interface {

	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Instantiate the class
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_ajax_rocket_lcp'              => 'add_data',
			'wp_ajax_nopriv_rocket_lcp'       => 'add_data',
			'wp_ajax_rocket_check_lcp'        => 'check_data',
			'wp_ajax_nopriv_rocket_check_lcp' => 'check_data',
		];
	}

	/**
	 * Callback for data received from beacon script
	 *
	 * @return void
	 */
	public function add_data() {
		foreach ( $this->factories as $factory ) {
			$factory->ajax()->add_data();
		}
	}

	/**
	 * Callback for checking data
	 *
	 * @return void
	 */
	public function check_data() {
		foreach ( $this->factories as $factory ) {
			$factory->ajax()->check_data();
		}
	}
}
