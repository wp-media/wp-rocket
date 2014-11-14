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

	$response = wp_remote_get( WP_ROCKET_WEB_CHECK, array( 'timeout' => 30 ) );

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
	if ( false !== $current && apply_filters( 'rocket_check_update', 12 * HOUR_IN_SECONDS ) > ( time() - $current ) ) {
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

	return $res;
}
/**
 * If we already know that an udate is available, try to autoupdate it.
 *
 * @since 2.4
 */
add_action( 'admin_footer', 'rkt_autoupdate', PHP_INT_MAX );
function rkt_autoupdate() {

	$plugin_transient = get_site_transient( 'update_plugins' );
	$c_key = get_rocket_option( 'consumer_key' );
	$transient = get_transient( 'rocket_warning_autoupdate' );
	if ( false === $transient && 
		isset( $plugin_transient->response['wp-rocket/wp-rocket.php']->package, $plugin_transient->response['wp-rocket/wp-rocket.php']->new_version ) && 
		sprintf( 'http://support.wp-rocket.me/%s/wp-rocket_%s.zip', $c_key, $plugin_transient->response['wp-rocket/wp-rocket.php']->new_version ) == $plugin_transient->response['wp-rocket/wp-rocket.php']->package
		)
	{
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		echo '<div style="display:none">'; // Avoid to display the update notifications from WordPress, this will change soon in WordPress core, wait and see.
			$title = __( 'Update Plugin' );
			$plugin = 'wp-rocket/wp-rocket.php';
			$nonce = 'upgrade-plugin_' . $plugin;
			$url = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin ); 			
			$upgrader = new Plugin_Upgrader( new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) ) );
			if ( $upgrader->upgrade( $plugin ) ) {
				$text = __( 'An autoupdate has been performed from v%1$s to v%2$s.', 'rocket' );
				$class = 'updated';
			} else {
				$text = __( 'We tried to autoupdate from v%1$s to v%2$s, but an error occured.', 'rocket' );
				$class = 'error';
			}
			$msg = sprintf( $text, WP_ROCKET_VERSION, $plugin_transient->response['wp-rocket/wp-rocket.php']->new_version );
			set_transient( 'rocket_warning_autoupdate', array( 'class' => $class, 'msg' => $msg ) );
			rocket_renew_box( 'rocket_warning_autoupdate' );
			$upgrader->after();
		echo '</div>';
	}
}