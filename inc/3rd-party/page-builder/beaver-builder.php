<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'FL_BUILDER_VERSION' ) ) :

/**
 * Purge the cache when the beaver builder layout is updated to update the minified files content & URL
 *
 * @since 2.8.6
 */
add_action( 'fl_builder_before_save_layout', '__rocket_beaver_builder_clean_domain', 10, 4 );
function __rocket_beaver_builder_clean_domain() {
    rocket_clean_minify();
    rocket_clean_domain();
}

endif;