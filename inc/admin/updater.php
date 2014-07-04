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
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
		return $r; // Not a plugin update request. Stop immediately.
	}

	$plugins = unserialize( $r['body']['plugins'] );

	if ( isset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ], $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active ) ] ) ) {
		unset( $plugins->plugins[ plugin_basename( WP_ROCKET_FILE ) ] );
		unset( $plugins->active[ array_search( plugin_basename( WP_ROCKET_FILE ), $plugins->active ) ] );
	}

	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

/**
 * Check Rocket updates twicedaily (like WP plugins)
 *
 * @since 1.0
 */
add_action( 'load-plugins.php', 'rocket_check_update', PHP_INT_MAX );
add_action( 'load-update.php', 'rocket_check_update', PHP_INT_MAX - 10 );
add_action( 'load-update-core.php', 'rocket_check_update', PHP_INT_MAX );
add_action( 'wp_update_plugins', 'rocket_check_update', PHP_INT_MAX );
function rocket_check_update()
{
	if ( defined( 'WP_INSTALLING' ) ) {
		return false;
	}

	$plugin_folder    = plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin_file      = basename( WP_ROCKET_FILE );
	$version          = true;
	$plugin_transient = null;
	add_filter( 'http_headers_useragent', 'rocket_user_agent' );
	$response = wp_remote_get( WP_ROCKET_WEB_CHECK, array( 'timeout' => 30 ) );
	remove_filter( 'http_headers_useragent', 'rocket_user_agent' );
	set_site_transient( 'update_wprocket', time() );

	if ( ! is_a( $response, 'WP_Error' ) && strlen( $response['body'] ) > 32 ) {

		list( $version, $url ) = explode( '|', $response['body'] );
		if ( version_compare( $version, WP_ROCKET_VERSION, '<=' ) ) {
			return false;
		}

		$plugin_transient = get_site_transient( 'update_plugins' );
		$temp_array = array(
			'slug'        => $plugin_folder,
			'new_version' => $version,
			'url'         => 'http://wp-rocket.me',
			'package'     => $url
		);

	} else {
		$temp_array = array();
	}

	if ( $plugin_transient ) {
		$temp_object = (object) $temp_array;
		$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
		set_site_transient( 'update_plugins', $plugin_transient );
	} else {
		return false;
	}
}

add_action( 'admin_init', '_maybe_update_rocket', PHP_INT_MAX );
function _maybe_update_rocket()
{
	$current = get_site_transient( 'update_wprocket' );
	if ( isset( $current ) && apply_filters( 'rocket_check_update', 12 * HOUR_IN_SECONDS ) > ( time() - $current ) ) {
		return;
	}

	rocket_check_update();
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
	if ( 'plugin_information' == $action && isset( $res->external, $args->slug ) && 'wp-rocket' == $args->slug && $res->external ) {

		add_filter( 'http_headers_useragent', 'rocket_user_agent' );
		$request = wp_remote_post( WP_ROCKET_WEB_INFO, array( 'timeout' => 30, 'action' => 'plugin_information', 'request' => serialize( $args ) ) );
		remove_filter( 'http_headers_useragent', 'rocket_user_agent' );

		if ( is_wp_error( $request ) ) {
			$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.','rocket' ), WP_ROCKET_WEB_SUPPORT ), $request->get_error_message() );
		} else {
			$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
			if ( ! is_object( $res ) && ! is_array( $res ) ) {
				$res = new WP_Error( 'plugins_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WP-Rocket.me or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'rocket' ), WP_ROCKET_WEB_SUPPORT ), wp_remote_retrieve_body( $request ) );
			}
		}

		if ( rocket_is_white_label() ) {
			$res = (array) $res;
			$res['name'] = get_rocket_option( 'wl_plugin_name' );
			$res['slug'] = sanitize_key( $res['name'] );
			$res['wl_plugin_URI']  = get_rocket_option( 'wl_plugin_URI' );
			$res['author']      = get_rocket_option( 'wl_author' );
			$res['author_profile']  = get_rocket_option( 'wl_author_URI' );
			$res['homepage']  = get_rocket_option( 'wl_author_URI' );
			$res['sections']['description'] = implode( "\n", get_rocket_option( 'wl_description' ) );
			$res['sections']['changelog'] = str_replace( array( 'wp-rocket', 'rocket_' ), array( $res['slug'], $res['slug'] . '_' ), $res['sections']['changelog'] );
			$res['sections']['changelog'] = str_replace( array( 'WP Rocket', 'WP&nbsp;Rocket', 'WP-Rocket' ), $res['name'], $res['sections']['changelog'] );
			unset( $res['sections']['installation'] );
			unset( $res['sections']['faq'] );
			unset( $res['contributors'] );
			$res = (object) $res;
		}
	}

	return $res;
}