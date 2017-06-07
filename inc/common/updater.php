<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * When WP sets the update_plugins site transient, we set our own transient, then see rocket_add_response_to_updates
 *
 * @since 2.6.5
 *
 * @param Object $value Site transient object.
 */
function rocket_check_update( $value ) {
	$timer_update_wprocket = (int) get_site_transient( 'update_wprocket' );
	$temp_object = get_site_transient( 'update_wprocket_response' );
	if ( ( ! isset( $_GET['rocket_force_update'] ) || defined( 'WP_INSTALLING' ) ) &&
		( 12 * HOUR_IN_SECONDS ) > ( time() - $timer_update_wprocket ) // retry in 12 hours.
	) {
		if ( is_object( $value ) && false !== $temp_object ) {
			if ( version_compare( $temp_object->new_version, WP_ROCKET_VERSION ) > 0 ) {
				$value->response[ $temp_object->plugin ] = $temp_object;
			} else {
				delete_site_transient( 'update_wprocket_response' );
			}
		}
		return $value;
	}

	if ( isset( $_GET['rocket_force_update'] ) ) {
		$_SERVER['REQUEST_URI'] = remove_query_arg( 'rocket_force_update' );
	}

	$plugin_folder	= plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin_file	= basename( WP_ROCKET_FILE );
	$version		= true;
	if ( ! $value ) {
		$value = new stdClass;
		$value->last_checked = time();
	}

	$response = wp_remote_get( WP_ROCKET_WEB_CHECK, array( 'timeout' => 30 ) );
	if ( ! is_a( $response, 'WP_Error' ) && 200 === $response['response']['code'] && strlen( $response['body'] ) > 32 ) {

		set_site_transient( 'update_wprocket', time() );

		list( $version, $url ) = explode( '|', $response['body'] );
		if ( version_compare( $version, WP_ROCKET_VERSION ) <= 0 ) {
			return $value;
		}

		$temp_array = array(
			'slug'			=> $plugin_folder,
			'plugin'		=> $plugin_folder . '/' . $plugin_file,
			'new_version'	=> $version,
			'url'			=> 'http://wp-rocket.me',
			'package'		=> $url,
		);

		$temp_object = (object) $temp_array;
		$value->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;

		set_site_transient( 'update_wprocket_response', $temp_object );
	} else {
		set_site_transient( 'update_wprocket', ( time() + ( 11 * HOUR_IN_SECONDS ) ) ); // retry in 1 hour in case of error..
	}
	return $value;
}
add_filter( 'site_transient_update_plugins', 'rocket_check_update', 1 );

/**
 * When WP deletes the update_plugins site transient or updates the plugins, we delete our own transients to avoid another 12 hours waiting
 *
 * @since 2.6.8
 *
 * @param string $transient Transient name.
 * @param object $value Transient object.
 */
function rocket_reset_check_update_timer( $transient = 'update_plugins', $value = null ) {
	// $value used by setted.
	if ( 'update_plugins' === $transient ) {
		if ( is_null( $value ) || is_object( $value ) && ! isset( $value->response ) ) {
			delete_site_transient( 'update_wprocket' );
		}
	}
}
add_action( 'wp_update_plugins', 'rocket_reset_check_update_timer', 9 ); // WP Cron.
add_action( 'deleted_site_transient', 'rocket_reset_check_update_timer' );
add_action( 'setted_site_transient', 'rocket_reset_check_update_timer' );
