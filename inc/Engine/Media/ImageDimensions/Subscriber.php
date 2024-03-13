<?php

namespace WP_Rocket\Engine\Media\ImageDimensions;

use WP_Rocket\Buffer\Tests;
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
	 * Buffer tests to run against current page, to decide if we can start the buffer or not.
	 *
	 * @var Tests
	 */
	private $buffer_tests;

	/**
	 * Subscriber constructor.
	 *
	 * @param ImageDimensions $dimensions Images dimensions class that handles all business logic.
	 */
	public function __construct( ImageDimensions $dimensions, Tests $buffer_tests ) {
		$this->dimensions   = $dimensions;
		$this->buffer_tests = $buffer_tests;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'specify_image_dimensions', 17 ],
			'template_redirect' => [ 'start_image_dimensions_buffer', 3 ],
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

	/**
	 * Start image dimensions buffer to add
	 *
	 * @return void
	 */
	public function start_image_dimensions_buffer() {
		if ( empty( $_GET['wpr_imagedimensions'] ) ) {
			return;
		}

		if ( ! $this->buffer_tests->can_process_any_buffer() ) {
			return;
		}

		add_filter( 'rocket_specify_image_dimensions', '__return_true' );

		ob_start( [ $this, 'specify_image_dimensions' ] );
	}
}
