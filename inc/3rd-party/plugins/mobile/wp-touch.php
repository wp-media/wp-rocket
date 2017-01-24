<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

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