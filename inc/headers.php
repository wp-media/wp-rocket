<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add Rocket informations into USER_AGENT
 *
 * since 1.1.0
 *
 */

add_filter( 'http_headers_useragent', 'rocket_user_agent' );
function rocket_user_agent( $user_agent )
{
	$new_ua = ';WP-Rocket|';
    $new_ua .=  WP_ROCKET_VERSION . '|';
    $new_ua .= str_pad( (string)get_rocket_option( 'consumer_key' ), 8, '0' ) . '|';
    $new_ua .= str_pad( (string)get_rocket_option( 'secret_key' ), 32, '0' ) . '|';
    $new_ua .= esc_url( home_url() ).'|;';
    return $user_agent . $new_ua;
}