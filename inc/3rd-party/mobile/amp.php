<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Remove Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP version of a post
 *
 * @since 2.7
 *
 */

add_action( 'wp', '_rocket_disable_options_on_amp' );
function _rocket_disable_options_on_amp() {
    if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
        remove_filter( 'rocket_buffer', 'rocket_exclude_deferred_js', 11 );
        remove_filter( 'rocket_buffer', 'rocket_dns_prefetch', 12 );
        remove_filter( 'rocket_buffer', 'rocket_minify_process', 13 );

        add_filter( 'do_rocket_lazyload', '__return_false' );
    }
}