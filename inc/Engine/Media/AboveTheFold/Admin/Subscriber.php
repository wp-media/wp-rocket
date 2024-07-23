<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Admin controller instance
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @param Controller $controller ATF Admin controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_saas_clean_url' => 'clean_url',
		];
	}

	/**
	 * Cleans rows for the current URL.
	 *
	 * @return void
	 */
	public function clean_url() {
		$this->controller->clean_url();
	}
}
