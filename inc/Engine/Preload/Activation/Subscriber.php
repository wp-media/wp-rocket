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
	 * Options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Subscriber constructor.
	 *
	 * @param Activation   $activation Activation instance.
	 * @param Options_Data $options Options.
	 */
	public function __construct( Activation $activation, Options_Data $options ) {
		$this->activation = $activation;
		$this->options    = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_activation'       => [ 'activate', 15 ],
			'wp_rocket_first_install' => 'first_install',
		];
	}

	/**
	 * Run actions on activation.
	 *
	 * @return void
	 */
	public function activate() {
		if ( ! $this->options->get( 'manual_preload', false ) ) {
			return;
		}
		$this->activation->activate();
	}

	/**
	 * Run actions on first install.
	 *
	 * @return void
	 */
	public function first_install() {
		$this->activation->activate();
	}
}
