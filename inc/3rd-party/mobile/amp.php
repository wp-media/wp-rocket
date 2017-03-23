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
function rocket_disable_options_on_amp() {
	if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		if ( function_exists( 'wp_resource_hints' ) ) {
			remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		} else {
			remove_filter( 'rocket_buffer', 'rocket_dns_prefetch_buffer', 12 );
		}

		remove_filter( 'rocket_buffer', 'rocket_insert_deferred_js', 11 );
		remove_filter( 'rocket_buffer', 'rocket_minify_process', 13 );
		remove_filter( 'rocket_buffer', 'rocket_defer_js', 14 );
		remove_filter( 'rocket_buffer', 'rocket_async_css', 15 );
		remove_filter( 'rocket_buffer', 'rocket_minify_html', 20 );

		add_filter( 'do_rocket_lazyload', '__return_false' );
	}
}
add_action( 'wp', 'rocket_disable_options_on_amp' );
