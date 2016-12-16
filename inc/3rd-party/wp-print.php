<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( function_exists( 'print_link' ) ) :

	add_action( 'wp', 'rocket_deactivate_lazyload_on_print_pages' );
	/**
	 * Conflict with WP-Print: don't apply LazyLoad on print pages
	 *
	 * @since 2.6.8
	 */
	function rocket_deactivate_lazyload_on_print_pages() {
		global $wp_query;

		if ( isset( $wp_query->query_vars['print'] ) ) {
			add_filter( 'do_rocket_lazyload', '__return_false' );
		}
	}

endif;
