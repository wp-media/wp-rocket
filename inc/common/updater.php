<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );
/**
 * Hack the output of update_plugins transient to add our update if available
 *
 * @since 2.6.5
 */
add_filter( 'site_transient_update_plugins', 'rocket_check_update', 100 );
function rocket_check_update( $value )
{
	global $pagenow;
	$timer_update_wprocket = (int) get_site_transient( 'update_wprocket' );
	if ( ( 'plugins.php' != $pagenow || ! isset( $_GET['rocket_force_update'] ) ) &&
		( defined( 'WP_INSTALLING' ) || 
		 ( 12 * HOUR_IN_SECONDS ) > ( time() - $timer_update_wprocket ) ) // retry in 12 hours
	) {
		return $value;
	}

	$plugin_folder	= plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin_file	= basename( WP_ROCKET_FILE );
	$version		= true;
	if ( ! $value ) {
		$value = new stdClass;
		$value->last_checked = time();
	}

	$response = wp_remote_get( WP_ROCKET_WEB_CHECK, array( 'timeout' => 30 ) );
	if ( ! is_a( $response, 'WP_Error' ) && strlen( $response['body'] ) > 32 ) {

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

		remove_all_filters( 'pre_set_site_transient_update_plugins' ); // we don't want anyone to change our value, sorry
		set_site_transient( 'update_plugins', $value );
	} else {
		set_site_transient( 'update_wprocket', ( time() + ( 11 * HOUR_IN_SECONDS ) ) ); // retry in 1 hour in case of error
	}

	return $value;
}