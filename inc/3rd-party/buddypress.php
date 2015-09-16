<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'BuddyPress' ) ) :

/**
 * Conflict with BuddyPress: don't apply LazyLoad on BuddyPress profil pages
 *
 * @since 2.6.9
 */
add_action( 'wp', '__deactivate_rocket_lazyload_on_buddypress_profil_pages' );
function __deactivate_rocket_lazyload_on_buddypress_profil_pages() {
	if ( function_exists( 'bp_is_my_profile' ) && bp_is_my_profile() ) {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}
}

endif;