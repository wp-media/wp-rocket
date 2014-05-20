<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );


/**
 * Add Rocket informations into USER_AGENT
 *
 * @since 1.1.0
 *
 */

function rocket_user_agent( $user_agent )
{
	$consumer_key = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_key'] ) ) {
		$consumer_key = $_POST[ WP_ROCKET_SLUG ]['consumer_key'];
	} elseif ( '' != (string) get_rocket_option( 'consumer_key' ) ) {
		$consumer_key = (string) get_rocket_option( 'consumer_key' );
	}	

	$consumer_email = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_email'] ) ) {
		$consumer_email = $_POST[ WP_ROCKET_SLUG ]['consumer_email'];
	} elseif ( '' != (string) get_rocket_option( 'consumer_email' ) ) {
		$consumer_email = (string) get_rocket_option( 'consumer_email' );
	}

	$WL = ! rocket_is_white_label() ? '' : '*';
    $new_ua = sprintf( ';WP-Rocket|%s%s|%s|%s|%s|;', WP_ROCKET_VERSION, $WL, $consumer_key, $consumer_email, esc_url( home_url() ) );

    return $new_ua;
}