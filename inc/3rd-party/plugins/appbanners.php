<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 2.2.4
 *
 * @param array $html_options An array of WP Rocket options.
 * @return array Array without the inline js minify option
 */
function rocket_deactivate_js_minifier_with_appbanner( $html_options ) {
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
		 unset( $html_options['jsMinifier'] );
	}
	 return $html_options;
}
add_filter( 'rocket_minify_html_options', 'rocket_deactivate_js_minifier_with_appbanner' );
