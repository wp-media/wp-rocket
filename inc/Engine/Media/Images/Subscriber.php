<?php

namespace WP_Rocket\Engine\Media\Images;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

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

	public function __construct( $frontend ) {
		$this->frontend = $frontend;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => 'specify_image_dimensions'
		];
	}

	public function specify_image_dimensions( $buffer ) {
		return $this->frontend->specify_image_dimensions( $buffer );
	}
}
