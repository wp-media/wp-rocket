<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add input fields in all widgets
 *
 * since 1.4.0
 *
 */

add_action( 'in_widget_form', 'rocket_in_widget_form', 10, 3 );
function rocket_in_widget_form( $t, $return, $instance )
{

    $instance = wp_parse_args( (array)$instance, array( 'rocket-partial-caching-interval' => '0', 'rocket-partial-caching-unit' => 'HOUR_IN_SECONDS' ) );
    $units = array( 'SECOND_IN_SECONDS'=>'seconde(s)', 'MINUTE_IN_SECONDS'=>'minute(s)', 'HOUR_IN_SECONDS'=>'heure(s)' );
    ?>
    <div>
        <input type="checkbox" class="rocketbox" id="<?php echo $t->get_field_id('rocket-partial-caching-active'); ?>"
            name="<?php echo $t->get_field_name('rocket-partial-caching-active'); ?>" value="1" 
            <?php checked( isset( $instance['rocket-partial-caching-active'] ) && $instance['rocket-partial-caching-active'] == 1, true ); ?> />
        <label for="<?php echo $t->get_field_id('rocket-partial-caching-active'); ?>"><?php _e( 'Mettre en cache partiel', 'wp-rocket' ); ?></label>
        <div class="rocketboxpanel hide-if-js">
            <?php _e( 'Pendant : ', 'wp-rocket' ); ?>
            <input id="<?php echo $t->get_field_id('rocket-partial-caching-interval'); ?>" 
                name="<?php echo $t->get_field_name('rocket-partial-caching-interval'); ?>" 
                type="number" value="<?php echo (int)$instance['rocket-partial-caching-interval']; ?>" 
                style="width: 60px" min="1" />
            <select id="<?php echo $t->get_field_id('rocket-partial-caching-unit'); ?>" 
                name="<?php echo $t->get_field_name('rocket-partial-caching-unit'); ?>" >
                <?php foreach( $units as $val => $title) { ?>
                    <option value="<?php echo $val; ?>" <?php selected( $instance['rocket-partial-caching-unit'], $val ); ?>><?php echo $title; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <?php
    $return = null;
    return array( $t, $return, $instance );
    
}

/**
 * Callback function for options update
 *
 * since 1.4.0
 *
 */

add_filter( 'widget_update_callback', 'rocket_widget_partial_caching_update', 10, 4 );
function rocket_widget_partial_caching_update( $instance, $new_instance, $old_instance, $_this ) 
{
    $instance['rocket-partial-caching-interval'] = isset($new_instance['rocket-partial-caching-interval']) ? (int)$new_instance['rocket-partial-caching-interval'] : 1;
    $instance['rocket-partial-caching-unit'] = isset($new_instance['rocket-partial-caching-unit']) ? $new_instance['rocket-partial-caching-unit'] : 'HOUR_IN_SECONDS';
    $instance['rocket-partial-caching-active'] = isset($new_instance['rocket-partial-caching-active']) ? (int)$new_instance['rocket-partial-caching-active'] : 0;
    if( isset( $old_instance['rocket-partial-caching-active'] ) && !isset($new_instance['rocket-partial-caching-active']) ) {
        $refresh_this = (array)get_transient( 'rocket-refresh-widgets-partial-caching' );
        $refresh_this[] = $_this->id;
        set_transient( 'rocket-refresh-widgets-partial-caching', array_filter( array_unique( $refresh_this ) ) );
    }
    return $instance;
}


/**
 * Inform Ajax that we deleted a widget, so he can now talk to Storage and tell him "hey dude, get out!"
 *
 * since 1.4.0
 *
 */

add_action( 'sidebar_admin_setup', 'rocket_sidebar_admin_setup' );
function rocket_sidebar_admin_setup() 
{
    global $wp_registered_widgets;
    if( isset( $_POST['delete_widget'], $_POST['widget-id'], $wp_registered_widgets[$_POST['widget-id']] ) && $_POST['delete_widget'] ) {
        $refresh_this = (array)get_transient( 'rocket-refresh-widgets-partial-caching' );
        $refresh_this[] = $_POST['widget-id'];
        set_transient( 'rocket-refresh-widgets-partial-caching', array_filter( array_unique( $refresh_this ) ) );
    } 
}