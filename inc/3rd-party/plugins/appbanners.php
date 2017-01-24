<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 2.2.4
 */
add_filter( 'rocket_minify_html_options', '__deactivate_rocket_jsMinifier_with_appbanner' );
function __deactivate_rocket_jsMinifier_with_appbanner( $html_options ) {
	 if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
	 	unset( $html_options['jsMinifier'] );
	 }
	 return $html_options;
}