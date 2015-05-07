<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Open a ticket support.
 *
 * @since 2.6
 */
add_action( 'wp_ajax_rocket_new_ticket_support', '__wp_ajax_rocket_new_ticket_support' );
function __wp_ajax_rocket_new_ticket_support() {
	if( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$response = wp_remote_post(
		WP_ROCKET_WEB_API . 'support/new-ticket.php',
		array(
			'method'  => 'POST',
			'timeout' => 10,
			'headers' => array(),
			'body'    => array(
				'data' => array(
					'user_email' 		   => sanitize_email(WP_ROCKET_EMAIL),
					'user_key' 		   	   => sanitize_key(WP_ROCKET_KEY),
					'user_website'		   => home_url(),
					'wp_version'           => $GLOBALS['wp_version'],
					'wp_active_plugins'    => rocket_get_active_plugins(),
					'wp_rocket_version'    => WP_ROCKET_VERSION,
					'wp_rocket_options'    => get_option( WP_ROCKET_SLUG ),
					'support_summary'	   => $_POST['summary'],
					'support_description'  => $_POST['description']
				)
			),
			'cookies' => array()
		)
	);

	if ( ! is_wp_error( $response ) ) {
        wp_send_json( wp_remote_retrieve_body( $response ) );
    } else {
	    wp_send_json( array( 'msg' => 'BAD_SERVER' ) );
    }
}