<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_faq', __( 'FAQ', 'rocket' ), '__return_false', 'rocket_faq' );
add_settings_field(
	'rocket_faq',
	__( 'FAQ', 'rocket' ),
	'rocket_include',
	'rocket_faq',
	'rocket_display_faq',
	array(
		'file'	=> 'faq',
	)
);