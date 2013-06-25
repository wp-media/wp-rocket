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
	$options = get_option( WP_ROCKET_SLUG );
	$new_ua = ';WP-Rocket|';
	$new_ua .=  WP_ROCKET_VERSION . '|';
	$new_ua .= ( isset( $options['consumer_key'] ) ? sanitize_key( $options['consumer_key'] ) : str_pad( '0', 8 ) ).'|';
	$new_ua .= ( isset( $options['secret_key'] ) ? sanitize_key( $options['secret_key'] ) : str_pad( '0', 32 ) ).'|';
	$new_ua .= esc_url( home_url() ).'|;';
	return $user_agent . $new_ua;
}