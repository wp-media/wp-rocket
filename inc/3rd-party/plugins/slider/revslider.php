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

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 */
add_action( 'init', '__rocket_cdn_on_sliders_with_lazyload' );
function __rocket_cdn_on_sliders_with_lazyload() {
	if ( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}