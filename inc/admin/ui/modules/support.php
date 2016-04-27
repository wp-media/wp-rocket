<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_support', __( 'Support', 'rocket' ), '__return_false', 'rocket_support' );
add_settings_field(
	'rocket_support_summary',
	__( 'Summary', 'rocket' ),
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		array(
			'type'			=> 'text',
			'label_for'		=> 'support_summary',
			'label_screen'	=> __( 'Summary', 'rocket' ),
		)
	)
);
add_settings_field(
	'rocket_support_description',
	__( 'Description', 'rocket' ),
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'support_description',
			'label_screen' => __( 'Description', 'rocket' ),
			'rows'	       => 10,
			'default'  	   => 	__( 'Please provide the specific url(s) where we can see each issue. e.g. the gallery doesn\'t work on this page: example.com/gallery-page', 'rocket' ) . "\n\n" .
								__( 'Please let us know how we will recognize the issue or can reproduce the issue. What is supposed to happen, and what is actually happening instead?', 'rocket' ) . "\n" .
								__( 'e.g. At the bottom of the post there are related posts which are supposed to have thumbnails, but no thumbnails are displaying.', 'rocket' )
		)
	)
);
add_settings_field(
	'rocket_support_documentation_validation',
	null,
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		'type'         => 'checkbox',
		'label'        => sprintf( __( 'I\'ve read the <a href="%s" target="_blank">documentation</a>, and I agree to allow WP Rocket to automatically detect my WordPress version and list of enabled plugins when I submit this form.', 'rocket' ), get_rocket_documentation_url() ),
		'label_for'    => 'support_documentation_validation'
	)
);
add_settings_field(
	'rocket_support_submit',
	null,
	'rocket_button',
	'rocket_support',
	'rocket_display_support',
	array(
		'button' => array(
        	'button_label' => __( 'Submit the ticket', 'rocket' ),
        	'button_id'    => 'submit-support-button',
        	'style'		   => 'primary'
        ),
	)
);