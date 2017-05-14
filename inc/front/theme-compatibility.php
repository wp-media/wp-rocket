<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

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
