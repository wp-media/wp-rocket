<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( function_exists( 'autoptimize_do_cachepurged_action' ) ) :

/**
 * Improvement with Autoptimize: clear the cache when Autoptimize's cache is cleared
 *
 * @since 2.7
 */
add_action( 'autoptimize_action_cachepurged', 'rocket_clean_domain' );

endif;