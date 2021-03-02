<?php

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * API Client.
	 *
	 * @var APIclient
	 */
	private $api;

	/**
	 * Options API.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Subscriber constructor.
	 *
	 * @param APIclient    $api API Client.
	 * @param Options_Data $options Options API.
	 */
	public function __construct( APIclient $api, Options_Data $options ) {
		$this->api     = $api;
		$this->options = $options;
	}

	/**
	 * Subscriber events
	 *
	 * @return string[]
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => 'optimize',
		];
	}

	/**
	 * Optimize HTML
	 *
	 * @param string $html HTML of the page.
	 *
	 * @return mixed
	 */
	public function optimize( $html ) {
		return $this->api->optimize(
			$html,
			[
				'tree_shake' => 1,
			]
		);
	}
}
