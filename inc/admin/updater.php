<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Excludes WP Rocket from WP updates
 *
 * @since 1.0
 */
add_filter( 'http_request_args', 'rocket_updates_exclude', 5, 2 );
function rocket_updates_exclude( $r, $url )
{
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) || ! isset( $r['body']['plugins'] ) ) {
		return $r; // Not a plugin update request. Stop immediately.
	}

	$plugins = maybe_unserialize( $r['body']['plugins'] );

	if ( isset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ], $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active ) ] ) ) {
		unset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ] );
		unset( $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active ) ] );
	}

	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

/**
 * Hack the returned object
 *
 * @since 1.0
 */
add_filter( 'plugins_api', 'rocket_force_info', 10, 3 );
function rocket_force_info( $bool, $action, $args )
{
	if ( 'plugin_information' == $action && 'wp-rocket' == $args->slug ) {
		return new stdClass();
	}
	return $bool;
}

/**
 * Hack the returned result with our content
 *
 * @since 1.0
 */
add_filter( 'plugins_api_result', 'rocket_force_info_result', 10, 3 );
function rocket_force_info_result( $res, $action, $args )
{
	if ( 'plugin_information' == $action && isset( $args->slug ) && 'wp-rocket' == $args->slug && isset( $res->external ) && $res->external ) {

		$request = wp_remote_post( WP_ROCKET_WEB_INFO, array( 'timeout' => 30, 'action' => 'plugin_information', 'request' => serialize( $args ) ) );

		if ( is_wp_error( $request ) ) {

			$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.','rocket' ), WP_ROCKET_WEB_SUPPORT ), $request->get_error_message() );

		} else {

			$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
			
			if ( ! is_object( $res ) && ! is_array( $res ) ) {
				$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'rocket' ), WP_ROCKET_WEB_SUPPORT ), wp_remote_retrieve_body( $request ) );
			}

		}

		if ( ! is_wp_error( $res ) && rocket_is_white_label() ) {

			$res = (array) $res;

			$res['name']					= get_rocket_option( 'wl_plugin_name' );
			$res['slug']					= sanitize_key( $res['name'] );	
			$res['author']					= get_rocket_option( 'wl_author' );
			$res['homepage']				= get_rocket_option( 'wl_author_URI' );
			$res['wl_plugin_URI']			= get_rocket_option( 'wl_plugin_URI' );
			$res['author_profile']			= get_rocket_option( 'wl_author_URI' );
			$res['sections']['changelog']	= str_replace( array( 'wp-rocket', 'rocket_' ), array( $res['slug'], $res['slug'] . '_' ), $res['sections']['changelog'] );
			$res['sections']['changelog']	= str_replace( array( 'WP Rocket', 'WP&nbsp;Rocket', 'WP-Rocket' ), $res['name'], $res['sections']['changelog'] );
			$res['sections']['description']	= implode( "\n", get_rocket_option( 'wl_description' ) );

			unset( $res['sections']['installation'], $res['sections']['faq'], $res['contributors'] );

			$res = (object) $res;

		}

	}
	
	return $res;
}