<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_faq', __( 'FAQ', 'rocket' ), '__return_false', 'faq_rocket' );
add_settings_field(
	'faq_rocket',
	__( 'FAQ', 'rocket' ),
	'rocket_include',
	'faq_rocket',
	'rocket_display_faq',
	array(
		'file'	=> 'faq',
	)
);