<?php

namespace WP_Rocket\Engine\Media\ImageDimensions;

use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * ImageDimensions instance
	 *
	 * @var ImageDimensions
	 */
	private $dimensions;

	/**
	 * Instantiate the class
	 *
	 * @param ImageDimensions $dimensions ImageDimensions instance.
	 */
	public function __construct( ImageDimensions $dimensions ) {
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
			'rocket_input_sanitize'        => [ 'sanitize_option', 10, 2 ],
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

	/**
	 * Sanitizes the option value when saving from the settings page
	 *
	 * @since 3.8
	 *
	 * @param array    $input    Array of sanitized values after being submitted by the form.
	 * @param Settings $settings Settings class instance.
	 * @return array
	 */
	public function sanitize_option( array $input, Settings $settings ) : array {
		return $this->dimensions->sanitize_option_value( $input, $settings );
	}
}
