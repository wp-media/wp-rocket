<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_apikey_options', __( 'License validation', 'rocket' ), '__return_false', 'rocket_apikey' );
add_settings_field(
	'rocket_api_key',
	__( 'API Key', 'rocket' ),
	'rocket_field',
	'rocket_apikey',
	'rocket_display_apikey_options',
	array(
		array(
			'type'			=> 'text',
			'label_for'		=> 'consumer_key',
			'label_screen'	=> __( 'API Key', 'rocket' ),
		),
		array(
			'type'			=> 'helper_help',
			'name'			=> 'consumer_key',
			'description'	=> __( 'Please enter the API key obtained after your purchase.', 'rocket' )
		),
	)
);
add_settings_field(
	'rocket_email',
	__( 'E-mail Address', 'rocket' ),
	'rocket_field',
	'rocket_apikey',
	'rocket_display_apikey_options',
	array(
		array(
			'type'         => 'email',
			'label_for'    => 'consumer_email',
			'label_screen' => __( 'E-mail Address', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'consumer_email',
			'description'  => __( 'The one used for the purchase, in your support account.', 'rocket' )
		),
	)
);