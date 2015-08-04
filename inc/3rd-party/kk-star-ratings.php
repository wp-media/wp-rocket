<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'BhittaniPlugin_kkStarRatings' ) ) :

/**
 * Conflict with kk Star Ratings: Clear the cache when a post gets rated.
 *
 * @since 2.5.3
 */
add_action( 'kksr_rate', '__rocket_clear_cache_on_kksr_rate' );
function __rocket_clear_cache_on_kksr_rate( $post_id ) {
	rocket_clean_post( $post_id );	
}

endif;