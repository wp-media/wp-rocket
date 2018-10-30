<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$current_theme = wp_get_theme();

if ( 'Avada' === $current_theme->get( 'Name' ) ) {
	// When Avada theme purge its own cache.
	add_action( 'avada_clear_dynamic_css_cache',  'rocket_clean_domain' );
	
	/**
	 * Conflict with Avada theme and WP Rocket CDN
	 *
	 * @since 2.6.1
	 *
	 * @param array  $vars An array of variables.
	 * @param string $handle Name of the avada resource.
	 * @return array updated array of variables
	 */
	function rocket_fix_cdn_for_avada_theme( $vars, $handle ) {
		if ( 'avada-dynamic' === $handle && get_rocket_option( 'cdn' ) ) {
	
			$src = get_rocket_cdn_url( get_template_directory_uri() . '/assets/less/theme/dynamic.less' );
			$vars['template-directory'] = sprintf( '~"%s"', dirname( dirname( dirname( dirname( $src ) ) ) ) );
			$vars['lessurl'] = sprintf( '~"%s"', dirname( $src ) );
		}
		return $vars;
	}
	add_filter( 'less_vars', 'rocket_fix_cdn_for_avada_theme', 11, 2 );

	$avada_options = get_option( 'avada_theme_options' );

	function rocket_exclude_avada_dynamic_css( $excluded_files ) {
		$upload_dir = wp_upload_dir();

		$excluded_files[] = rocket_clean_exclude_file( $upload_dir['baseurl'] . '/fusion-styles/(.*)' );

		return $excluded_files;
	}
	add_filter( 'rocket_exclude_cache_busting', 'rocket_exclude_avada_dynamic_css' );
}
