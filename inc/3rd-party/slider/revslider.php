<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Revolution Slider: don't minify inline script when HTML minification is activated
 *
 * @since 2.6.8
 *
 * @param array $html_options WP Rocket options array.
 * @return array Updated WP Rocket options
 */
function rocket_deactivate_js_minifier_with_revslider( $html_options ) {
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'RevSliderFront' ) ) {
	 	unset( $html_options['jsMinifier'] );
	}
	 return $html_options;
}
add_filter( 'rocket_minify_html_options', 'rocket_deactivate_js_minifier_with_revslider' );
