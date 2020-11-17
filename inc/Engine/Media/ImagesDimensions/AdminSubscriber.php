<?php

namespace WP_Rocket\Engine\Media\ImagesDimensions;

use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * ImagesDimensions instance
	 *
	 * @var ImagesDimensions
	 */
	private $dimensions;

	/**
	 * Instantiate the class
	 *
	 * @param ImagesDimensions $dimensions ImageDimensions instance.
	 */
	public function __construct( ImagesDimensions $dimensions ) {
		$this->dimensions = $dimensions;
	}

	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_first_install_options' => [ 'add_option', 14 ],
		];
	}

	/**
	 * Add the images dimensions option to the WP Rocket options array
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		return $this->dimensions->add_option( $options );
	}
}
