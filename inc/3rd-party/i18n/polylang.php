<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if( defined( 'POLYLANG_VERSION' ) && POLYLANG_VERSION ) :

/**
 * Conflict with Polylang: Clear the whole cache when the "The language is set from content" option is activated.
 *
 * @since 2.6.8
 */
add_action( 'after_rocket_clean_domain', '_rocket_force_clean_domain_on_polylang' );
function _rocket_force_clean_domain_on_polylang() {
	$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];
    
    if ( isset( $pll ) && 0 === $pll->options['force_lang'] ) {
        rocket_clean_cache_dir();
    }
}

endif;