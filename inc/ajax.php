<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Used to send the new data when time is up
 *
 * since 1.4.0
 *
 */

add_action( 'wp_ajax_nopriv_rocket_get_refreshed_fragments', 'rocket_get_refreshed_fragments' );
add_action( 'wp_ajax_rocket_get_refreshed_fragments', 'rocket_get_refreshed_fragments' );
function rocket_get_refreshed_fragments()
{
	if( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'WPRXMLHttpRequest' )
		return;

	global $wp_registered_sidebars, $wp_registered_widgets;

	$fragments = -1;

	// Get all sidebars
	$sidebars_widgets = wp_get_sidebars_widgets();

	// If no sidebar, return to get a coffee
	if ( empty( $sidebars_widgets ) )
			return false;

	// Remove inactive widgets
	if( isset( $sidebars_widgets['wp_inactive_widgets'] ) )
		unset( $sidebars_widgets['wp_inactive_widgets'] );

	foreach ( $sidebars_widgets as $sidebar_id => $widgets )
	{

		$sidebar = $wp_registered_sidebars[$sidebar_id];

		foreach( $widgets as $widget_id )
		{

			// Get widget basename (ex: recent-comments)
			$widget_basename = $wp_registered_widgets[$widget_id]['callback'][0]->id_base;

			// Get widgets save options
			$widget_options  = get_option( 'widget_' . $widget_basename );
			$widget_options  = $widget_options[$wp_registered_widgets[$widget_id]['params'][0]['number']];

			//
			if( !isset( $widget_options['rocket-partial-caching-interval'] ) ||
				!isset( $widget_options['rocket-partial-caching-unit'] ) ||
				!isset( $widget_options['rocket-partial-caching-active'] ) ||
				(int)$widget_options['rocket-partial-caching-active']!=1 )
				continue;

			$interval = (int)( $widget_options['rocket-partial-caching-interval'] * constant( $widget_options['rocket-partial-caching-unit'] ) );
			if( isset( $widget_options['rocket-partial-caching-active'] ) && $widget_options['rocket-partial-caching-active']==1 &&
				( !isset( $_POST[$widget_id] ) || ( time() - $_POST[$widget_id] > 0 ) ) )
			{

				$params = array_merge(
					array( array_merge( $sidebar, array('widget_id' => $widget_id, 'widget_name' => $wp_registered_widgets[$widget_id]['name']) ) ),
					(array)$wp_registered_widgets[$widget_id]['params']
				);

				//
				$callback = $wp_registered_widgets[$widget_id]['callback'];
				if ( is_callable($callback) )
				{

					if( !is_array( $fragments ) )
						$fragments = array();
					ob_start();
					$params[0]['before_widget'] = '';
					$params[0]['after_widget']  = '';
					call_user_func_array($callback, $params);
					$fragments[$widget_id]['content'] = ob_get_clean();
					$fragments[$widget_id]['interval'] = (int)(time() + $interval);
				}

			}

		}

	}

	unset( $_POST['action'] );

	$get_transient_widgets = array_filter( (array)get_transient( 'rocket-refresh-widgets-partial-caching' ) );
	if( !empty( $get_transient_widgets ) ){
		if( !is_array( $fragments ) )
			$fragments = array();
		foreach( $get_transient_widgets as $widget_id) {
			$fragments[$widget_id]['content'] = '';
			$fragments[$widget_id]['interval'] = 0;
		}
		delete_transient( 'rocket-refresh-widgets-partial-caching' );
	}

	if( is_array( $fragments ) && !empty( $_POST ) )
	{
		
		$fragments = array_intersect_key( $fragments, $_POST );
		foreach( $_POST as $k=>$v )
			$_POST[$k] = array( 'content'=>'', 'interval'=>0 );
		$fragments = wp_parse_args( $fragments, $_POST );

	}
	wp_send_json( $fragments );
}
