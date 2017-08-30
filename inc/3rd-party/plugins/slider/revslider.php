<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

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

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 */
function rocket_cdn_on_sliders_with_lazyload() {
	if ( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}
add_action( 'init', 'rocket_cdn_on_sliders_with_lazyload' );
