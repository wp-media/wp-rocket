<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'WP_POSTRATINGS_VERSION' ) ) :

/**
 * Conflict with WP-PostRatings: Clear the cache when a post gets rated.
 *
 * @since 2.6.6
 */
add_action( 'rate_post', '__rocket_clear_cache_on_wp_postratings_rate', 10, 2 );
function __rocket_clear_cache_on_wp_postratings_rate( $user_id, $post_id ) {
	rocket_clean_post( $post_id );	
}

endif;