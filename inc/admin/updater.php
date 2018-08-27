<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Excludes WP Rocket from WP updates
 *
 * @since 1.0
 *
 * @param array  $request An array of HTTP request arguments.
 * @param string $url The request URL.
 * @return array Updated array of HTTP request arguments.
 */
function rocket_updates_exclude( $request, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) || ! isset( $request['body']['plugins'] ) ) {
		return $request; // Not a plugin update request. Stop immediately.
	}

	$plugins = maybe_unserialize( $request['body']['plugins'] );

	if ( isset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ], $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active, true ) ] ) ) {
		unset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ] );
		unset( $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active, true ) ] );
	}

	$request['body']['plugins'] = maybe_serialize( $plugins );
	return $request;
}
add_filter( 'http_request_args', 'rocket_updates_exclude', 5, 2 );

/**
 * Hack the returned object
 *
 * @since 1.0
 *
 * @param false|object|array $bool The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Install API.
 * @param object             $args Plugin API arguments.
 * @return false|object|array Empty object if slug is WP Rocket, default value otherwise
 */
function rocket_force_info( $bool, $action, $args ) {
	if ( 'plugin_information' === $action && 'wp-rocket' === $args->slug ) {
		return new stdClass();
	}
	return $bool;
}
add_filter( 'plugins_api', 'rocket_force_info', 10, 3 );

/**
 * Hack the returned result with our content
 *
 * @since 1.0
 *
 * @param object|WP_Error $res Response object or WP_Error.
 * @param string          $action The type of information being requested from the Plugin Install API.
 * @param object          $args Plugin API arguments.
 * @return object|WP_Error Updated response object or WP_Error
 */
function rocket_force_info_result( $res, $action, $args ) {
	if ( 'plugin_information' === $action && isset( $args->slug ) && 'wp-rocket' === $args->slug && isset( $res->external ) && $res->external ) {

		$request = wp_remote_post(
			WP_ROCKET_WEB_INFO, array(
				'timeout' => 30,
				'action'  => 'plugin_information',
				'request' => serialize( $args ),
			)
		);

		if ( is_wp_error( $request ) ) {
			// translators: %s is an URL.
			$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, <a href="%s">contact support</a>.', 'rocket' ), rocket_get_external_url( 'support', array(
				'utm_source' => 'wp_plugin',
				'utm_medium' => 'wp_rocket',
			) ) ), $request->get_error_message() );
		} else {
			$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );

			if ( ! is_object( $res ) && ! is_array( $res ) ) {
				// translators: %s is an URL.
				$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, <a href="%s">contact support</a>.', 'rocket' ), rocket_get_external_url( 'support', array(
					'utm_source' => 'wp_plugin',
					'utm_medium' => 'wp_rocket',
				) ) ), wp_remote_retrieve_body( $request ) );
			}
		}
	}

	return $res;
}
add_filter( 'plugins_api_result', 'rocket_force_info_result', 10, 3 );
