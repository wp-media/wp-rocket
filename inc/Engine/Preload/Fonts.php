<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Fonts preload.
 *
 * @since 3.6
 */
class Fonts implements Subscriber_Interface {

	/**
	 * WP Rocket Options instance.
	 *
	 * @since 3.6
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [];
	}
}
