<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

use WP_Rocket\Admin\Options_Data;

class Options {
	private $options;

	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	public function get_options() {
		wp_register_ability(
			'wp-media/get-options',
			[
				'label' => __( 'Get WP Rocket options', 'rocket' ),
				'description' => __( 'Get all WP Rocket options and their current values.', 'rocket' ),
				'category' => 'wp-rocket-options',
				'output_schema' => [
					'type' => 'array',
					'items' => [],
				],
				'execute_callback' => [ $this, 'get_filtered_options' ],
				'permission_callback' => function() {
					return current_user_can( 'rocket_manage_options' );
				},
				'show_in_rest' => true,
			]
		);
	}

	public function get_filtered_options() {
		$options = $this->options->get_options();

		return $options;
	}
}
