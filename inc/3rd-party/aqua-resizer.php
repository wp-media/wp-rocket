<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Aqua Resizer & IrishMiss Framework: Apply CDN without blank src!!
 *
 * @since 2.5.8 Add compatibility with IrishMiss Framework
 * @since 2.5.5
 */
function rocket_cdn_on_aqua_resizer() {
	if ( function_exists( 'aq_resize' ) || function_exists( 'miss_display_image' ) ) {
		remove_filter( 'wp_get_attachment_url' , 'rocket_cdn_file', PHP_INT_MAX );
		add_filter( 'rocket_lazyload_html', 'rocket_add_cdn_on_custom_attr' );
	}
}
add_action( 'init', 'rocket_cdn_on_aqua_resizer' );
