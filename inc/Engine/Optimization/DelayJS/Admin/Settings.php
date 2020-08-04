<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {
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

	public function add_options( $options ) {
		$options = (array) $options;

		$options['delay_js']         = 1;
		$options['delay_js_scripts'] = $this->defaults;

		return $options;
	}

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

	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.7', '>' ) ) {
			return;
		}

		$this->options->set( 'delay_js', 0 );

		update_option( 'wp_rocket_settings', $this->options->get_options() );
	}

	public function restore_defaults() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		$this->options->set( 'delay_js_scripts', $this->defaults );

		return update_option( rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ), $this->options->get_options() );
	}
}
