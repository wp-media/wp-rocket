<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Open a ticket support.
 *
 * @since 2.6
 */
function wp_ajax_rocket_new_ticket_support() {
	// rocket_capability is a typo (should have been rocket_capacity).
	if ( ! isset( $_POST['_wpnonce'], $_POST['summary'], $_POST['description'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wp_rocket-options' ) ||
		! current_user_can( apply_filters_deprecated( 'rocket_capability', array( 'manage_options' ), '2.8.9', 'rocket_capacity' ) ) ||
		! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		) {
		return;
	}

	$response = wp_remote_post(
		WP_ROCKET_WEB_API . 'support/new-ticket.php',
		array(
			'timeout' => 10,
			'body'    => array(
				'data' => array(
					'user_email'           => defined( 'WP_ROCKET_EMAIL' ) ? sanitize_email( WP_ROCKET_EMAIL ) : '',
					'user_key'             => defined( 'WP_ROCKET_KEY' ) ? sanitize_key( WP_ROCKET_KEY ) : '',
					'user_website'         => home_url(),
					'wp_version'           => $GLOBALS['wp_version'],
					'wp_active_plugins'    => rocket_get_active_plugins(),
					'wp_rocket_version'    => WP_ROCKET_VERSION,
					'wp_rocket_options'    => get_option( WP_ROCKET_SLUG ),
					'support_summary'      => $_POST['summary'],
					'support_description'  => $_POST['description'],
				),
			),
		)
	);

	if ( ! is_wp_error( $response ) ) {
		wp_send_json( wp_remote_retrieve_body( $response ) );
	} else {
		wp_send_json(
			array(
				'msg' => 'BAD_SERVER',
			)
		);
	}
}
add_action( 'wp_ajax_rocket_new_ticket_support', 'wp_ajax_rocket_new_ticket_support' );

/**
 * Documentation suggestions based on the summary input from the new ticket support form.
 *
 * @since 2.6
 */
function wp_ajax_rocket_helpscout_live_search() {
	if ( current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
		$query = filter_input( INPUT_POST, 'query' );
		$response = wp_remote_post(
			WP_ROCKET_WEB_MAIN . 'tools/wp-rocket/helpscout/livesearch.php',
			array(
				'timeout'   => 10,
				'body'      => array(
					'query' => esc_html( wp_strip_all_tags( $query, true ) ),
					'lang'  => get_locale(),
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			wp_send_json( wp_remote_retrieve_body( $response ) );
		}
	}
}
add_action( 'wp_ajax_rocket_helpscout_live_search', 'wp_ajax_rocket_helpscout_live_search' );

/**
 * Generates the critical CSS
 *
 * @since 2.11
 * @author Remy Perona
 */
function wp_ajax_rocket_generate_critical_css() {
	if ( false === check_ajax_referer( 'wp_rocket-options', '_wpnonce', false ) ) {
		wp_send_json_error( __( 'Nonce verification failed', 'rocket' ), 403 );
	}

	$response = wp_remote_post(
		WP_ROCKET_CRITICAL_CSS_GENERATOR,
		array(
			'body' => home_url(),
		)
	);

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		wp_send_json_error(  $response, wp_remote_retrieve_response_code( $response ) );
	}

	$critical_css = wp_remote_retrieve_body( $response );

	wp_send_json_success(  $critical_css );
}
add_action( 'wp_ajax_rocket_generate_critical_css', 'wp_ajax_rocket_generate_critical_css' );
