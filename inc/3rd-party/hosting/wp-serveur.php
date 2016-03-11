<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'DB_HOST' ) && strpos( DB_HOST , '.wpserveur.net' ) !== false ) :

/**
 * Conflict with WP Serveur hosting: don't apply inline JS on all pages
 *
 * @since 2.6.11
 */
add_action( 'rocket_minify_html_options', '__deactivate_inline_js_on_wp_serveur' );
function __deactivate_inline_js_on_wp_serveur( $html_options ) {
	if ( isset( $html_options['jsMinifier'] ) ) {
		unset( $html_options['jsMinifier'] );
	}
	return $html_options;
}

/**
  * Allow to purge Varnish on WP Serveur websites
  *
  * @since 2.6.11
 */
add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

/**
  * Don't display the Varnish options tab for WP Serveur users
  *
  * @since 2.7
 */
add_filter( 'rocket_display_varnish_options_tab', '__return_false' );

endif;