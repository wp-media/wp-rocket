<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if ( defined( 'ELEMENTOR_VERSION' ) ) :
	/**
	 * Excludes Elementor scripts from JS minification
	 *
	 * @since 2.9.5
	 * @source Nicolas Mollet https://github.com/wp-media/wp-rocket/pull/302
	 *
	 * @param Array $excluded_js An array of JS handles enqueued in WordPress.
	 * @return Array the updated array of handles
	 */
	function rocket_exclude_js_elementor( $excluded_js ) {
		$excluded_js[] = '/(.*)/elementor/(.*).js';
		return $excluded_js;
	}
	add_filter( 'rocket_exclude_js', 'rocket_exclude_js_elementor' );
endif;
