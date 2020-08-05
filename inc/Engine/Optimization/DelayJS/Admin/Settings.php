<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {
	/**
	 * Array of defaults scripts to delay
	 *
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
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
		$options = (array) $options;

		$options['delay_js']         = 1;
		$options['delay_js_scripts'] = $this->defaults;

		return $options;
	}

	/**
	 * Gets the data to populate the view for the restore defaults button
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	public function get_button_data() {
		return [
			'type'       => 'button',
			'action'     => 'rocket_delay_js_restore_defaults',
			'attributes' => [
				'label'      => __( 'Restore Defaults', 'rocket' ),
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
				],
			],
		];
	}

	/**
	 * Sets the delay_js option to zero when updating to 3.7
	 *
	 * @since 3.7
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.7', '>' ) ) {
			return;
		}

		$this->options->set( 'delay_js', 0 );

		update_option( 'wp_rocket_settings', $this->options->get_options() );
	}

	/**
	 * Restores the delay_js_scripts option to the default value
	 *
	 * @since 3.7
	 *
	 * @return bool|array
	 */
	public function restore_defaults() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		return implode( "\n", $this->defaults );
	}
}
