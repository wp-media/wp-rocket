<?php

namespace WP_Rocket\ThirdParty\Plugins\Ecommerce;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;

class JudgeMeWooCommerceSubscriber implements Event_Manager_Aware_Subscriber_Interface {


	/**
	 * The WordPress Event Manager
	 *
	 * @var Event_Manager;
	 */
	protected $event_manager;

	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	public static function get_subscribed_events() {
		$events = [];
		if ( class_exists( 'JudgeMe' ) ) {
			$events['rocket_delay_js_exclusions'] = 'show_notempty_product_gallery_with_delayJS';
		}

		return $events;
	}

	public function show_not_empty_product_gallery_with_delayJS( array $exclusions = [] ) {
		$exclusions[] = 'window.jdgmSettings';
		return $exclusions;
	}
}
