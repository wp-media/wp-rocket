<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\Media\Fonts\Admin\Settings as FontsSettings;
use WP_Rocket\Event_Management\Subscriber_Interface;


class Subscriber implements Subscriber_Interface {
	/**
	 * Fonts Settings instance
	 *
	 * @var FontsSettings
	 */
	private $settings;

	/**
	 * Instantiate the class
	 *
	 * @param FontsSettings $settings Fonts Settings instance.
	 */
	public function __construct( FontsSettings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_first_install_options' => [ 'add_option', 16 ],
			'rocket_input_sanitize'        => [ 'sanitize_option', 10, 2 ],
		];
	}

	/**
	 * Add the images dimensions option to the WP Rocket options array
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_option( array $options ): array {
		return $this->settings->add_option( $options );
	}

	/**
	 * Sanitizes the option value when saving from the settings page
	 *
	 * @param array    $input    Array of sanitized values after being submitted by the form.
	 * @param Settings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_option( array $input, Settings $settings ): array {
		return $this->settings->sanitize_option_value( $input, $settings );
	}
}
