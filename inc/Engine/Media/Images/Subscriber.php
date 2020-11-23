<?php

namespace WP_Rocket\Engine\Media\Images;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

/**
 * Images Subscriber
 *
 * @since 3.8
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Frontend instance
	 *
	 * @var Frontend
	 */
	private $frontend;

	/**
	 * Subscriber constructor.
	 *
	 * @param Frontend $frontend Frontend class that handles all business logic.
	 */
	public function __construct( Frontend $frontend ) {
		$this->frontend = $frontend;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => ['specify_image_dimensions', 19],
		];
	}

	/**
	 * Update images that have no width/height with real dimentions.
	 *
	 * @param string $buffer Page HTML content.
	 *
	 * @return string Page HTML content after update.
	 */
	public function specify_image_dimensions( $buffer ) {
		Logger::debug("specify_image_dimensions_start");
		if ( rocket_bypass() ) {
			return $buffer;
		}

		return $this->frontend->specify_image_dimensions( $buffer );
	}
}
