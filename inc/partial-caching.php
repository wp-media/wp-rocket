<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add WP Rocket attribut in before_widget
 *
 * since 1.4.0
 *
 */
 
add_filter( 'dynamic_sidebar_params', 'rocket_dynamic_sidebar_params' );
function rocket_dynamic_sidebar_params( $params )
{

    global $wp_registered_widgets;
    $widget_id           = $params[0]['widget_id'];
    $widget_obj          = $wp_registered_widgets[$widget_id];
    $widget_opt          = get_option($widget_obj['callback'][0]->option_name);
    $widget_num          = $widget_obj['params'][0]['number'];
    $widget_data		 = $widget_opt[$widget_num];
    $before_widget       = $params[0]['before_widget'];
    $after_widget        = $params[0]['after_widget'];
    $widget_opt_active   = isset( $widget_data['rocket-partial-caching-active'] ) ? $widget_data['rocket-partial-caching-active'] : false;
    $widget_opt_interval = isset( $widget_data['rocket-partial-caching-interval'] ) ? (int)$widget_data['rocket-partial-caching-interval'] : false;
    $widget_opt_unit     = isset( $widget_data['rocket-partial-caching-unit'] ) ? $widget_data['rocket-partial-caching-unit'] : false;
    
	
    // Check if the widget should be in partial cache
	if ( $widget_opt_active && $widget_opt_interval > 0 )
	{
		$interval = (int)( $widget_opt_interval * constant( $widget_opt_unit ) );

		// Si les widgets de la sidebar ne possède pas d'argument "before_widget",
		// On ajoute automatiquement l'id du widget et l'attribut data-partial-caching
		if( empty( $before_widget ) )
		{
			$before_widget = '<div id="' . $widget_id . '" data-partial-caching="true" aria-live="polite">';
			$after_widget  = '</div>';
		}
		else
		{
			//
			if( preg_match( '/id=[\'"](.*)[\'"]/' , $before_widget ) )
				$before_widget = preg_replace( '/id=[\'"](.*)[\'"]/', '/id="$1" data-partial-caching="true" aria-live="polite"/', $before_widget );
			else
				$before_widget = str_replace( '>', ' id="' . $widget_id . '" data-partial-caching="true" aria-live="polite">', $before_widget );
		}

	}
	
	$params[0]['before_widget'] = $before_widget;
	$params[0]['after_widget']  = $after_widget;
    return $params;
}



/**
 * Remove HTML content of elements that used partial caching
 *
 * @since 1.4.0
 *
 */

add_filter( 'rocket_buffer', 'rocket_remove_partial_caching', 12 );
function rocket_remove_partial_caching( $buffer )
{
	
	// Get all tag with data-partial-caching attribute
	preg_match_all( '/<(\w+)[^>]*data-partial-caching="true"[^>]*>(.*?)<\/\\1>/si', $buffer, $partial_caching_tags_match, PREG_SET_ORDER );
	
	if( !$partial_caching_tags_match )
		return $buffer;
	
	foreach( $partial_caching_tags_match as $tag ) 
	{
		
		// 
		$partial_caching_without_content = str_replace( $tag[2], '', $tag[0] );
		
		//
		$buffer = str_replace( $tag[0], $partial_caching_without_content, $buffer );
	}
	
	return $buffer;
}



/**
 * JavaScript code to read LocalStorage and send ajax informations
 *
 * since 1.4.0
 *
 */
 
add_action( 'wp_enqueue_scripts', 'rocket_add_ajax_for_partial_cache', 0 );
function rocket_add_ajax_for_partial_cache()
{
	wp_enqueue_script( 
		'rocket_ajax_partial_caching', 
		WP_ROCKET_FRONT_JS_URL . 'partial-caching.' . WP_ROCKET_VERSION . '.js', 
		array(), 
		false, 
		true 
	);
	
	wp_localize_script( 
		'rocket_ajax_partial_caching', 
		'rocket_l10n', 
		array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) 
	);
}