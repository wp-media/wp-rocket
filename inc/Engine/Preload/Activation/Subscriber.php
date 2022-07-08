<?php

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {


	/**
	 * @var Activation
	 */
	protected $activation;

	/**
	 * @param Activation $activation
	 */
	public function __construct( Activation $activation ) {
		$this->activation = $activation;
	}


	public static function get_subscribed_events() {
		return [
			'rocket_activation' => 'activate',
		];
	}

	public function activate() {
		$this->activation->activate();
	}
}
