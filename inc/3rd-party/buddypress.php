<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'BuddyPress' )
	 && function_exists( 'bp_is_my_profile' )
	 && function_exists( 'bp_is_group_creation_step' )
	 && function_exists( 'bp_is_group_admin_screen' )
	 && function_exists( 'bp_is_user_change_avatar' ) ) :

/**
 * Conflict with BuddyPress: don't apply LazyLoad on BuddyPress profil pages
 *
 * @since 2.6.9
 */
add_filter( 'do_rocket_lazyload', '__deactivate_rocket_lazyload_on_buddypress_profil_pages' );
function __deactivate_rocket_lazyload_on_buddypress_profil_pages( $run_filter ) {
	if ( bp_is_my_profile()
		 || bp_is_group_creation_step( 'group-avatar' )
		 || bp_is_group_admin_screen( 'group-avatar' )
		 || bp_is_user_change_avatar() ) {
		$run_filter = false;
	}

	return $run_filter;
}

endif;