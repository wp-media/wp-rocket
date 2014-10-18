<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with WP Touch: deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 */
add_action( 'init', '__deactivate_rocket_lazyload_with_wptouch' );
function __deactivate_rocket_lazyload_with_wptouch()
{
    if ( ( function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing() ) || ( function_exists( 'bnc_wptouch_is_mobile' ) && bnc_wptouch_is_mobile() ) )
    {
		add_filter( 'do_rocket_lazyload', '__return_false' );
    }
}

/**
 * Conflict with LayerSlider: don't add width and height attributes on all images
 *
 * @since 2.1
 */
add_action( 'layerslider_ready', '__deactivate_rocket_specify_image_dimensions_with_layerslider' );
function __deactivate_rocket_specify_image_dimensions_with_layerslider()
{
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 2.2.4
 */
add_filter( 'rocket_minify_html_options', '__deactivate_jsMinifier_with_appbanner' );
function __deactivate_jsMinifier_with_appbanner( $html_options ) {
	 if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
	 	unset( $html_options['jsMinifier'] );
	 }
	 return $html_options;
}