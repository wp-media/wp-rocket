<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

class Settings {
	private $defaults = [];

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
}
