<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Conflict with WP Touch : deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 *
 */

add_action( 'init', '__deactivate_rocket_lazyload_in_wptouch' );
function __deactivate_rocket_lazyload_in_wptouch()
{

    if( function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing() )
    {
		add_filter( 'do_rocket_lazyload', '__return_false' );
    }

}