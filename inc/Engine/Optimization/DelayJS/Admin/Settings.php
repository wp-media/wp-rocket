<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Abstract_Render;

class Settings extends Abstract_Render {
	private $defaults = [];

	public function add_options( $options ) {
		$options = (array) $options;

		$options['delay_js'] = 1;
		$options['delay_js_scripts'] = $defaults;

		return $options;
	}

	public function display_restore_defaults_button() {
		$this->render_action_button(
			'button',
			'rocket_delay_js_restore_defaults',
			[
				'label'      => __( 'Restore Defaults', 'rocket' ),
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
				],
			]
		);
	}
}
