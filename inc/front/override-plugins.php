<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with WP Touch : deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 */
add_action( 'init', '__deactivate_rocket_lazyload_with_wptouch' );
function __deactivate_rocket_lazyload_with_wptouch()
{
    if( (function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing()) || (function_exists( 'bnc_wptouch_is_mobile' ) && bnc_wptouch_is_mobile()) )
    {
		add_filter( 'do_rocket_lazyload', '__return_false' );
    }
}

/**
 * Conflict with LayerSlider : don't add width and height attributes on all images
 *
 * @since 2.1
 */
add_action( 'layerslider_ready', '__deactivate_rocket_specify_image_dimensions_with_layerslider' );
function __deactivate_rocket_specify_image_dimensions_with_layerslider()
{
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}

/**
 * Conflict with WP-Filebase Download Manager & Download Monitor : don't add rules to skip 404 handling by WordPress on static files
 *
 * @since 2.2.1
 */
add_action( 'init', '__disable_htaccess_skip_404' );
function __disable_htaccess_skip_404() {
	if ( ( defined( 'WPFB' ) && WPFB ) || defined( 'DLM_VERSION' ) && DLM_VERSION ) {
		add_filter( 'rocket_htaccess_handle_404', '__return_false' );
	}
}