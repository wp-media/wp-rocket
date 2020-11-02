<?php

namespace WP_Rocket\Engine\Optimization\SaaS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $api;
	private $options;

	public function __construct( APIclient $api, Options_Data $options ) {
		$this->api     = $api;
		$this->options = $options;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => 'optimize',
		];
	}

	public function optimize( $html ) {
		return $this->api->optimize(
			$html,
			[
				'tree_shake' => 1,
			]
		);
	}
}
