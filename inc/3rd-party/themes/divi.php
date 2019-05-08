<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$current_theme = wp_get_theme();

if ( 'Divi' === $current_theme->get( 'Name' ) || 'Divi' === $current_theme->get( 'Template' ) ) :
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

	/**
	 * Excludes Divi main CSS files from Optimize CSS Delivery
	 *
	 * Prevents a display issue with the Divi Blog Module
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @param array $excluded_css An array of excluded CSS
	 * @return array
	 */
	function rocket_exclude_async_css_divi( $excluded_css ) {
		if ( ! get_rocket_option( 'async_css' ) ) {
			return $excluded_css;
		}

		$excluded_css[] = rocket_clean_exclude_file( get_stylesheet_uri() );
		$excluded_css[] = rocket_clean_exclude_file( get_template_directory_uri() . '/style.css' );

		return $excluded_css;
	}
	add_filter( 'rocket_exclude_async_css', 'rocket_exclude_async_css_divi' );
	add_filter( 'rocket_exclude_cache_busting', 'rocket_exclude_async_css_divi' );
	add_filter( 'rocket_exclude_css', 'rocket_exclude_async_css_divi' );
endif;
