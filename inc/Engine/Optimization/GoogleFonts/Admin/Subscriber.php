<?php

namespace WP_Rocket\Engine\Optimization\GoogleFonts\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Google Fonts Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Google Fonts Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_settings_tools_content'      => 'display_google_fonts_enabler',
			'wp_ajax_rocket_enable_google_fonts' => 'enable_google_fonts',
		];
	}

	/**
	 * Displays the Google Fonts Optimization section in the tools tab
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function display_google_fonts_enabler() {
		$this->settings->display_google_fonts_enabler();
	}

	/**
	 * Callback method for the AJAX request to enable Google Fonts Optimization
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function enable_google_fonts() {
		$this->settings->enable_google_fonts();
	}
}
