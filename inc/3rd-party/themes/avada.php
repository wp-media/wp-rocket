<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

// When Avada theme purge its own cache
add_action( 'avada_clear_dynamic_css_cache',  'rocket_clean_domain' );	
add_filter( 'less_vars', '__rocket_fix_cdn_for_avada_theme', 11, 2 );
/**
 * Conflict with Avada theme and WP Rocket CDN
 *
 * @since 2.6.1
 */
function __rocket_fix_cdn_for_avada_theme( $vars, $handle ) {
	if ( $handle === 'avada-dynamic' && get_rocket_option( 'cdn' ) ) { 
		$src = get_rocket_cdn_url( get_template_directory_uri() . '/assets/less/theme/dynamic.less' );
		$vars['template-directory'] = sprintf( '~"%s"', dirname( dirname( dirname( dirname( $src ) ) ) ) );
		$vars['lessurl'] = sprintf( '~"%s"', dirname( $src ) );
	}
	return $vars;
}

