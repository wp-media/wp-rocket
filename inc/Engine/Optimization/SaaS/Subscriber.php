<?php

namespace WP_Rocket\Engine\Optimization\SaaS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\SaaS\Warmup\ResourcesFinder;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $api;
	private $options;
	private $finder;

	public function __construct( APIclient $api, Options_Data $options, ResourcesFinder $finder ) {
		$this->api     = $api;
		$this->options = $options;
		$this->finder  = $finder;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [
				[ 'warmup' ],
			],
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

	public function warmup( $html ) {
		$this->finder->data( [ 'html' => $html ] )->dispatch();

		return $html;
	}
}
