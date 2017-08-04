<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_support', __( 'Support', 'rocket' ), '__return_false', 'rocket_support' );

/**
 * Panel caption
 */
add_settings_field(
	'rocket_support_panel',
	false,
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		array(
			'type'         => 'helper_panel_description',
			'name'         => 'support_panel_caption',
			'description'  => sprintf(
				'<span class="dashicons dashicons-sos" aria-hidden="true"></span><strong>%1$s</strong>',
				/* translators: line breaks recommended, but not mandatory; %s = localised docs URL */
				sprintf( __( 'The <a href="%s" target="_blank">WP Rocket documentation</a> provides answers to many common questions, check it out!<br>Our Happiness Rocketeers will help you sort out any questions you may have regarding this plugin.<br>Please help us to help you and provide detailed information.', 'rocket' ), get_rocket_documentation_url() )
			),
		),
	)
);

/**
 * Summary
 */
add_settings_field(
	'rocket_support_summary',
	__( 'Summary:', 'rocket' ),
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		array(
			'type'         => 'helper_help',
			'description'  => __( 'In one sentence: What is going on, or wrong?', 'rocket' )
		),
		array(
			'type'         => 'text',
			'label_for'    => 'support_summary',
			'label_screen' => __( 'Summary', 'rocket' ),
		),
	)
);

/**
 * Description
 */
add_settings_field(
	'rocket_support_description',
	__( 'Description:', 'rocket' ),
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		array(
			'type'         => 'helper_help',
			'description'  =>
			/* translators: line breaks recommended, but not mandatory  */
			__( '<strong>Now be specific!</strong><br>We have pre-filled the form with some questions for you to phrase your description along.<br>We speak English, French, German, Italian, Serbian, and Spanish.', 'rocket' )
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'support_description',
			'label_screen' => __( 'Description', 'rocket' ),
			'rows'	       => 10,
			'default'  	   =>
			/* translators: default field value, no HTML allowed! */
			__( "- What did you do?\n- What did you see?\n- What had you expected to see?\n- Where can we see your issue?\n- What steps do we need to perform in order to see it?", 'rocket' )
		),
	)
);

/**
 * Confirm documentation
 */
add_settings_field(
	'rocket_support_documentation_validation',
	null,
	'rocket_field',
	'rocket_support',
	'rocket_display_support',
	array(
		'type'         => 'checkbox',
		'label'        => sprintf(
			/* translators: %s = localised docs URL */
			__( 'I have read the <a href="%s" target="_blank">documentation</a> and agree that WP Rocket automatically detects my WordPress version and list of active plugins when I send this form.', 'rocket' ),
			get_rocket_documentation_url()
		),
		'label_for'    => 'support_documentation_validation',
	)
);

/**
 * Send
 */
add_settings_field(
	'rocket_support_submit',
	null,
	'rocket_button',
	'rocket_support',
	'rocket_display_support',
	array(
		'button' => array(
			'button_label' => _x( 'Send your ticket', 'button text', 'rocket' ),
			'button_id'    => 'submit-support-button',
			'style'		   => 'primary',
		),
	)
);
