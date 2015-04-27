<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_support', __( 'Support', 'rocket' ), '__return_false', 'support' );
add_settings_field(
	'support',
	__( 'Support', 'rocket' ),
	'rocket_button',
	'support',
	'rocket_display_support',
	array(
			'button'=>array(
				'button_label'	=> __( 'Visit the Support', 'rocket' ),
				'url'			=> 'http://wp-rocket.me/support/',
				'style'			=> 'link',
				),
			'helper_help'=>array(
				'name'			=> 'support',
				'description'	=> __( 'If none of the FAQ answers resolves your problem, you can send your issue to our free support. We will reply as soon as possible.', 'rocket')
			),
	)
);