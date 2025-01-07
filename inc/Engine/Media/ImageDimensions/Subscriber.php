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
			'rocket_buffer'                   => [ 'specify_image_dimensions', 17 ],
			'rocket_performance_hints_buffer' => 'image_dimensions_query_string',
		];
	}

	/**
	 * Update images that have no width/height with real dimensions.
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
	 * Add image dimensions if the query string is in the URL.
	 *
	 * @param string $buffer Page HTML content.
	 *
	 * @return string
	 */
	public function image_dimensions_query_string( $buffer ): string {
		if ( empty( $_GET['wpr_imagedimensions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $buffer;
		}

		add_filter( 'rocket_specify_image_dimensions', '__return_true' );

		return $this->dimensions->specify_image_dimensions( $buffer );
	}
}
