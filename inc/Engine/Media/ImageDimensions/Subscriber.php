<?php

namespace WP_Rocket\Engine\Media\ImageDimensions;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Images Subscriber
 *
 * @since 3.8
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Images dimensions instance
	 *
	 * @var ImageDimensions
	 */
	private $dimensions;

	/**
	 * Subscriber constructor.
	 *
	 * @param ImageDimensions $dimensions Images dimensions class that handles all business logic.
	 */
	public function __construct( ImageDimensions $dimensions ) {
		$this->dimensions = $dimensions;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'specify_image_dimensions', 17 ],
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
		if ( rocket_bypass() ) {
			return $buffer;
		}

		return $this->dimensions->specify_image_dimensions( $buffer );
	}
}
