<?php

defined( 'ABSPATH' ) || exit;

/**
 * Removes Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP version of a post with the AMP for WordPress plugin from Auttomatic
 *
 * @since 2.8.10 Compatibility with wp_resource_hints in WP 4.6
 * @since 2.7
 *
 * @author Remy Perona
 */
function rocket_disable_options_on_amp() {
	global $wp_filter;

	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		add_filter( 'do_rocket_lazyload', '__return_false' );
		unset( $wp_filter['rocket_buffer'] );

		// this filter is documented in inc/front/protocol.php.
		$do_rocket_protocol_rewrite = apply_filters( 'do_rocket_protocol_rewrite', false ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( ( get_rocket_option( 'do_cloudflare', 0 ) && get_rocket_option( 'cloudflare_protocol_rewrite', 0 ) || $do_rocket_protocol_rewrite ) ) {
			remove_filter( 'rocket_buffer', 'rocket_protocol_rewrite', PHP_INT_MAX );
			remove_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );
		}
	}
}
add_action( 'wp', 'rocket_disable_options_on_amp' );
