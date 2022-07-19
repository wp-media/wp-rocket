<?php

namespace WP_Rocket\Engine\Preload\Activation;

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
			'rocket_activation' => 'activate',
		];
	}

	/**
	 * Run actions on activation.
	 *
	 * @return void
	 */
	public function activate() {
		$this->activation->activate();
	}
}
