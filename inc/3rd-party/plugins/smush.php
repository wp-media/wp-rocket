<?php
defined('ABSPATH') || die('Cheatin&#8217; uh?');
use WP_Rocket\Logger\Logger;

/**
 * Disable WP Rocket Lazyload when activating Wp Smush and values are already in the database.
 *
 * @since  3.4.2
 * @author Soponar Cristina
 */
if ( defined( 'WP_SMUSH_VERSION' ) ) {
    function rocket_smush_maybe_deactivate_lazyload()
    {
        $lazyLoadOption = get_option( WP_SMUSH_PREFIX . 'settings' );
        $lazyload       = isset( $lazyLoadOption[ 'lazy_load' ] ) ? $lazyLoadOption[ 'lazy_load' ] : false;

        if ( ! $lazyload ) {
            return;
        }

        update_rocket_option( 'lazyload', 0 );
    }
    add_action( 'update_option_wp-smush-settings', 'rocket_smush_maybe_deactivate_lazyload', 11 );
}

/**
 * Disable WP Rocket lazyload when activating WP Smush and values are already in the database.
 *
 * @since  3.4.2
 * @author Soponar Cristina
 */
function rocket_activate_smush()
{
    $lazyLoadOption = get_option( WP_SMUSH_PREFIX . 'settings' );
    $lazyload       = isset( $lazyLoadOption[ 'lazy_load' ] ) ? $lazyLoadOption[ 'lazy_load' ] : false;

    if ( ! $lazyload ) {
        return;
    }

    update_rocket_option( 'lazyload', 0 );
}
add_action( 'activate_wp-smushit/wp-smush.php', 'rocket_activate_smush', 11 );

/**
 * Disable WP Rocket lazyload fields if WP Smush lazyload is enabled
 *
 * @since  3.4.2
 * @author Soponar Cristina
 *
 * @return bool
 */
function rocket_maybe_disable_lazyload_smush()
{
    if ( ! defined('WP_SMUSH_VERSION') ) {
        return ;
    }

    $lazyLoadOption = get_option( WP_SMUSH_PREFIX . 'settings' );
    $lazyload       = isset( $lazyLoadOption[ 'lazy_load' ] ) ? $lazyLoadOption[ 'lazy_load' ] : false;

    if ( ! $lazyload ) {
        return false;
    }

    if ( is_plugin_active( 'wp-smushit/wp-smush.php' ) && ! empty( $lazyload ) ) {
        return true;
    }

    return false;
}
