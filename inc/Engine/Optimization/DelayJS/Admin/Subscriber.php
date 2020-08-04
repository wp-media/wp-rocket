<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options'                 => 'add_options',
			'rocket_after_textarea_field_delay_js_scripts' => 'display_restore_defaults_button',
		];
	}

	public function add_options( $options ) {
		$this->settings->add_options( $options );
	}

	public function display_restore_defaults_button() {
		$this->settings->display_restore_defaults_button();
	}
}
