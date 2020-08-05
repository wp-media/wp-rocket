<?php

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [
				[ 'delay_js', 23 ]
			]
		];
	}

	public function delay_js( $html ) {

	}

}
