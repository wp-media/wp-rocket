<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Renew all boxes for everyone if $uid is missing
 *
 * @since 1.1.10
 *
 */

function rocket_renew_all_boxes( $uid=0, $keep_this=array() )
{
	if( (int)$uid>0 )
	{
		delete_user_meta( $uid, 'rocket_boxes' );
	}
	else
	{
		global $wpdb;
		$query = 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key="rocket_boxes"';
		// do not use $wpdb->delete because WP 3.4 is required!
		$wpdb->query( $query );
	}

	// $keep_this works only for the current user
	if( !empty( $keep_this ) )
	{
		if( is_array( $keep_this ) )
		{
			foreach( $keep_this as $kt )
			{
				rocket_dismiss_box( $kt );
			}

		}
		else
		{
			rocket_dismiss_box( $keep_this );
		}

	}

}



/**
 * Renew a dismissed error box admin side
 *
 * @since 1.1.10
 *
 */

function rocket_renew_box( $function, $uid=0 )
{
	global $current_user;
	$uid = $uid==0 ? $current_user->ID : $uid;
	$actual = get_user_meta( $uid, 'rocket_boxes', true );

	if( $actual )
	{
		unset( $actual[array_search( $function, $actual )] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}

}


/**
 * Is this version White Labeled?
 *
 * @since 2.1
 *
 */

function rocket_is_white_label()
{
	$names = array( 'wl_plugin_name', 'wl_plugin_URI', 'wl_description', 'wl_author', 'wl_author_URI' );
	$options = '';
	foreach( $names as $value )
	{
		$options .= !is_array( get_rocket_option( $value ) ) ? get_rocket_option( $value ) : reset( ( get_rocket_option( $value ) ) );
	}
	return 'a509cac94e0cd8238b250074fe802b90' != md5( $options );
}