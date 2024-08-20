<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Processor Instance.
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Instantiate the class
	 *
	 * @param Processor $processor Processor Instance.
	 */
	public function __construct( Processor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_ajax_rocket_beacon'              => 'add_data',
			'wp_ajax_nopriv_rocket_beacon'       => 'add_data',
			'wp_ajax_rocket_check_beacon'        => 'check_data',
			'wp_ajax_nopriv_rocket_check_beacon' => 'check_data',
		];
	}

	/**
	 * Callback for data received from beacon script
	 *
	 * @return void
	 */
	public function add_data() {
		$this->processor->add_data();
	}

	/**
	 * Callback for checking data
	 *
	 * @return void
	 */
	public function check_data() {
		$this->processor->check_data();
	}
}
