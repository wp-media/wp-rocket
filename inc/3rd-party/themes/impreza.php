<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$current_theme = wp_get_theme();

if ( 'impreza' === strtolower( $current_theme->get( 'Name' ) ) || 'impreza' === strtolower( $current_theme->get( 'Template' ) ) ) {
	/**
	 * Excludes Impreza files from minification/combine, defer and cache busting
	 *
	 * @since 3.2
	 * @author TheZoker
	 *
	 * @param array $excluded_js Array of JS filepaths to be excluded.
	 * @return array
	 */
	function rocket_exclude_js_impreza( $excluded_js ) {
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/framework/js/us.core.min.js' );

		return $excluded_js;
	}
    add_filter( 'rocket_exclude_js', 'rocket_exclude_js_impreza' );
    add_filter( 'rocket_exclude_defer_js', 'rocket_exclude_js_impreza' );
    add_filter( 'rocket_exclude_cache_busting', 'rocket_exclude_js_impreza' );
}
