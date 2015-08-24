<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Conflict with Revolution Slider: don't minify inline script when HTML minification is activated
 *
 * @since 2.6.8
 */
add_filter( 'rocket_minify_html_options', '__deactivate_rocket_jsMinifier_with_revslider' );
function __deactivate_rocket_jsMinifier_with_revslider( $html_options ) {
	 if ( isset( $html_options['jsMinifier'] ) && class_exists( 'RevSliderFront' ) ) {
	 	unset( $html_options['jsMinifier'] );
	 }
	 return $html_options;
}