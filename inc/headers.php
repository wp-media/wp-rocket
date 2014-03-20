<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Add Rocket informations into USER_AGENT
 *
 * @since 1.1.0
 *
 */

add_filter( 'http_headers_useragent', 'rocket_user_agent' );
function rocket_user_agent( $user_agent )
{
	if ( '' != (string)get_rocket_option( 'consumer_key' ) ) {
		$consumer_key = (string)get_rocket_option( 'consumer_key' );
	} elseif ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_key'] ) ) {
		$consumer_key = $_POST[ WP_ROCKET_SLUG ]['consumer_key'];
	} else {
		$consumer_key = '';
	}	
	if ( '' != (string)get_rocket_option( 'secret_key' ) ) {
		$secret_key = (string)get_rocket_option( 'secret_key' );
	} elseif ( isset( $_POST[ WP_ROCKET_SLUG ]['secret_key'] ) ) {
		$secret_key = $_POST[ WP_ROCKET_SLUG ]['secret_key'];
	} else {
		$secret_key = '';
	}
	$WL = !rocket_is_white_label() ? '' : '*';
	$new_ua = ';WP-Rocket|';
    $new_ua .=  WP_ROCKET_VERSION . $WL . '|';
    $new_ua .= str_pad( $consumer_key, 8, '0' ) . '|';
    $new_ua .= str_pad( $secret_key, 32, '0' ) . '|';
    $new_ua .= esc_url( home_url() ).'|;';
    return $user_agent . $new_ua;
}