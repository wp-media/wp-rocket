<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_faq', __( 'FAQ', 'rocket' ), '__return_false', 'faq' );
add_settings_field(
	'faq',
	__( 'FAQ', 'rocket' ),
	'rocket_include',
	'faq',
	'rocket_display_faq',
	array(
		'file'	=> 'faq',
	)
);