<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @param Controller $controller Controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_ajax_rocket_beacon'              => 'add_beacon_data',
			'wp_ajax_nopriv_rocket_beacon'       => 'add_beacon_data',
			'wp_ajax_rocket_check_beacon'        => 'check_beacon_data',
			'wp_ajax_nopriv_rocket_check_beacon' => 'check_beacon_data',
		];
	}

	/**
	 * Callback for data received from beacon script
	 *
	 * @return void
	 */
	public function add_beacon_data() {
		$this->controller->add_beacon_data();
	}

	/**
	 * Callback for checking datas from beacon
	 *
	 * @return void
	 */
	public function check_beacon_data() {
		$this->controller->check_beacon_data();
	}
}
