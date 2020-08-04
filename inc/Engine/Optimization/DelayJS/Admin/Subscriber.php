<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber extends Abstract_Render implements Subscriber_Interface {
	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options'                 => 'add_options',
			'rocket_after_textarea_field_delay_js_scripts' => 'display_restore_defaults_button',
			'wp_rocket_upgrade'                            => [ 'set_option_on_update', 12, 2 ],
			'wp_ajax_rocket_restore_delay_js_defaults'     => 'restore_defaults',
			'rocket_safe_mode_reset_options'               => 'add_options',
		];
	}

	public function add_options( $options ) {
		$this->settings->add_options( $options );
	}

	public function display_restore_defaults_button() {
		$data = $this->settings->get_button_data();

		$this->render_action_button(
			$data['type'],
			$data['action'],
			$data['attributes']
		);
	}

	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );
	}

	public function restore_defaults() {
		$result = $this->settings->restore_defaults();

		if ( $result ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}
