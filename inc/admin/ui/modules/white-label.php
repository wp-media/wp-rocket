<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_white_label', __( 'White Label', 'rocket' ), '__return_false', 'rocket_white_label' );
add_settings_field(
	'rocket_wl_plugin_name',
	__( 'Plugin Name:', 'rocket' ),
	'rocket_field',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		array(
			'type'         => 'text',
			'name'         => 'wl_plugin_name',
			'label_for'    => 'wl_plugin_name',
			'label_screen' => __( 'Plugin Name:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wl_plugin_URI',
	__( 'Plugin URI:', 'rocket' ),
	'rocket_field',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		array(
			'type'         => 'text',
			'name'         => 'wl_plugin_URI',
			'label_for'    => 'wl_plugin_URI',
			'label_screen' => __( 'Plugin URI:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wl_description',
	__( 'Description:', 'rocket' ),
	'rocket_field',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		array(
			'type'         => 'textarea',
			'name'         => 'wl_description',
			'label_for'    => 'wl_description',
			'label_screen' => __( 'Description:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wl_author',
	__( 'Author:', 'rocket' ),
	'rocket_field',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		array(
			'type'         => 'text',
			'name'         => 'wl_author',
			'label_for'    => 'wl_author',
			'label_screen' => __( 'Author:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wl_author_URI',
	__( 'Author URI:', 'rocket' ),
	'rocket_field',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		array(
			'type'         => 'text',
			'name'         => 'wl_author_URI',
			'label_for'    => 'wl_author_URI',
			'label_screen' => __( 'Author URI:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wl_warning',
	'',
	'rocket_button',
	'rocket_white_label',
	'rocket_display_white_label',
	array(
		'button' => array(
			'button_label' => __( 'Reset White Label values to default', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_resetwl' ), 'rocket_resetwl' ),
		),
		'helper_warning' => array(
			'name'         => 'wl_warning',
			'description'  => __( 'The Support tab and all links to WP Rocket’s documentation will be hidden when you customize these fields.', 'rocket' ),
		),
	)
);
