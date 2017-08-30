<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Conflict with WP Touch: deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 */
function rocket_deactivate_lazyload_with_wptouch() {
	if ( ( function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing() ) || ( function_exists( 'bnc_wptouch_is_mobile' ) && bnc_wptouch_is_mobile() ) ) {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}
}
add_action( 'init', 'rocket_deactivate_lazyload_with_wptouch' );
