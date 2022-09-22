<?php

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {


	/**
	 * Activation instance.
	 *
	 * @var Activation
	 */
	protected $activation;

	/**
	 * Subscriber constructor.
	 *
	 * @param Activation $activation Activation instance.
	 */
	public function __construct( Activation $activation ) {
		$this->activation = $activation;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_rocket_first_install' => 'first_install',
		];
	}

	/**
	 * Run actions on first install.
	 *
	 * @return void
	 */
	public function first_install() {
		$this->activation->preload_activation();
	}
}
