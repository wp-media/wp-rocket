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
			$src                        = get_rocket_cdn_url( get_template_directory_uri() . '/assets/less/theme/dynamic.less' );
			$vars['template-directory'] = sprintf( '~"%s"', dirname( dirname( dirname( dirname( $src ) ) ) ) );
			$vars['lessurl']            = sprintf( '~"%s"', dirname( $src ) );
		}
		return $vars;
	}
	add_filter( 'less_vars', 'rocket_fix_cdn_for_avada_theme', 11, 2 );

	/**
	 * Exclude fusion styles from cache busting to prevent cache dir issues
	 *
	 * @author Remy Perona
	 *
	 * @param array $excluded_files An array of excluded files.
	 * @return array
	 */
	function rocket_exclude_avada_dynamic_css( $excluded_files ) {
		$upload_dir = wp_upload_dir();

		$excluded_files[] = rocket_clean_exclude_file( $upload_dir['baseurl'] . '/fusion-styles/(.*)' );

		return $excluded_files;
	}
	add_filter( 'rocket_exclude_cache_busting', 'rocket_exclude_avada_dynamic_css' );

	/**
	 * Deactivate WP Rocket lazyload if Avada lazyload is enabled
	 *
	 * @since 3.3.4
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous Avada option value.
	 * @param string $value New Avada option value.
	 * @return void
	 */
	function rocket_avada_maybe_deactivate_lazyload( $old_value, $value ) {
		if ( empty( $old_value['lazy_load'] ) && ! empty( $value['lazy_load'] ) ) {
			update_rocket_option( 'lazyload', 0 );
		}
	}
	add_action( 'update_option_fusion_options', 'rocket_avada_maybe_deactivate_lazyload', 10, 2 );
}

/**
 * Disable WP Rocket lazyload field if Avada lazyload is enabled
 *
 * @since 3.3.4
 * @author Remy Perona
 *
 * @return bool
 */
function rocket_avada_maybe_disable_lazyload() {
	$avada_options = get_option( 'fusion_options' );
	$current_theme = wp_get_theme();

	if ( 'Avada' === $current_theme->get( 'Name' ) && ! empty( $avada_options['lazy_load'] ) ) {
		return true;
	}

	return false;
}

/**
 * Clears WP Rocket's cache after Avada's Fusion Patcher flushes their caches
 *
 * @since 3.3.5
 * @author Vasilis Manthos
 */
function rocket_avada_clear_cache_fusion_patcher() {
	rocket_clean_domain();
}
add_action( 'fusion_cache_reset_after', 'rocket_avada_clear_cache_fusion_patcher' );
