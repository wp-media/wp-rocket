<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

$current_theme = wp_get_theme();

if ( 'Divi' === $current_theme->get( 'Name' ) ) :
	/**
	 * Excludes Divi's Salvatorre script from JS minification
	 *
	 * Exclude it to prevent an error after minification/concatenation
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param Array $excluded_js An array of JS paths to be excluded.
	 * @return Array the updated array of paths
	 */
	function rocket_exclude_js_divi( $excluded_js ) {
		if ( defined( 'ET_BUILDER_URI' ) ) {
			$excluded_js[] = str_replace( home_url(), '', ET_BUILDER_URI ) . '/scripts/salvattore.min.js';
		}

		return $excluded_js;
	}
	add_filter( 'rocket_exclude_js', 'rocket_exclude_js_divi' );
endif;
