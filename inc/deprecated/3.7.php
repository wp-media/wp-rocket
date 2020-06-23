<?php

defined( 'ABSPATH' ) || exit;

/**
 * Require deprecated classes.
 */
require_once __DIR__ . '/vendors/classes/class-minify-html.php';
require_once __DIR__ . '/subscriber/admin/Optimization/class-minify-html-subscriber.php';


/**
 * Conflict with WP Serveur hosting: don't apply inline JS on all pages.
 *
 * @since 3.7 deprecated
 * @since 2.6.11
 *
 * @param array $html_options WP Rocket options array.
 *
 * @return array Updated WP Rocket options array.
 */
function rocket_deactivate_inline_js_on_wp_serveur( $html_options ) {
	_deprecated_function( __FUNCTION__ . '()', '3.7' );
	if ( isset( $html_options['jsMinifier'] ) ) {
		unset( $html_options['jsMinifier'] );
	}

	return $html_options;
}
