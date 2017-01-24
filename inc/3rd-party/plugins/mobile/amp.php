<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Removes Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP version of a post with the AMP for WordPress plugin from Auttomatic
 *
 * @since 2.8.10 Compatibility with wp_resource_hints in WP 4.6
 * @since 2.7
 *
 * @author Remy Perona
 */
add_action( 'wp', '_rocket_disable_options_on_amp' );
function _rocket_disable_options_on_amp() {
    if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
        remove_filter( 'rocket_buffer', 'rocket_exclude_deferred_js', 11 );

        if ( function_exists( 'wp_resource_hints' ) ) {
            remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
        } else {
            remove_filter( 'rocket_buffer', '__rocket_dns_prefetch_buffer', 12 );
        }

        remove_filter( 'rocket_buffer', 'rocket_minify_process', 13 );

        add_filter( 'do_rocket_lazyload', '__return_false' );
    }
}