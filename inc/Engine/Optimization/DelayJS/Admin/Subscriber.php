<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings      Settings instance.
	 * @param string   $template_path Template path.
	 */
	public function __construct( Settings $settings, $template_path ) {
		parent::__construct( $template_path );

		$this->settings = $settings;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options'                 => 'add_options',
			'rocket_after_textarea_field_delay_js_scripts' => 'display_restore_defaults_button',
			'wp_rocket_upgrade'                            => [
				[ 'set_option_on_update', 13, 2 ],
				[ 'option_update_3_7_2', 13, 2 ],
				[ 'option_update_3_7_4', 13, 2 ],
				[ 'option_update_3_7_6_1', 13, 2 ],
			],
			'wp_ajax_rocket_restore_delay_js_defaults'     => 'restore_defaults',
			'rocket_safe_mode_reset_options'               => 'add_options',
		];
	}

	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) {
		return $this->settings->add_options( $options );
	}

	/**
	 * Displays the restore defaults button under the textarea field
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function display_restore_defaults_button() {
		$data = $this->settings->get_button_data();

		$this->render_action_button(
			$data['type'],
			$data['action'],
			$data['attributes']
		);
	}

	/**
	 * Sets the delay_js option to zero when updating to 3.7
	 *
	 * @since 3.7
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );
	}

	/**
	 * Update the delay_js options when updating to 3.7.2.
	 *
	 * @since 3.7.2
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_2( $new_version, $old_version ) {
		$this->settings->option_update_3_7_2( $old_version );
	}

	/**
	 * Update the delay_js options when updating to 3.7.4
	 *
	 * @since 3.7.4
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_4( $new_version, $old_version ) {
		$this->settings->option_update_3_7_4( $old_version );
	}

	/**
	 * Restore the delay_js options to default when updating from 3.7.6.
	 *
	 * @since 3.7.6.1
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_6_1( $new_version, $old_version ) {
		$this->settings->option_update_3_7_6_1( $old_version );
	}

	/**
	 * AJAX callback to restore the default value for the delay JS scripts
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function restore_defaults() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$result = $this->settings->restore_defaults();

		if ( false === $result ) {
			wp_send_json_error();

			return;
		}

		wp_send_json_success( $result );
	}
}
