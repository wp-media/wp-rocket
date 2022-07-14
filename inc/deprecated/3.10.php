<?php

defined( 'ABSPATH' ) || exit;

class_alias( '\WP_Rocket\deprecated\Engine\Media\Embeds\EmbedsSubscriber', '\WP_Rocket\Engine\Media\Embeds\EmbedsSubscriber' );
class_alias( '\WP_Rocket\Engine\Admin\Database\Optimization','\WP_Rocket\Admin\Database\Optimization' );
class_alias( '\WP_Rocket\Engine\Admin\Database\OptimizationProcess','\WP_Rocket\Admin\Database\Optimization_Process'  );
class_alias( '\WP_Rocket\Engine\Admin\Database\ServiceProvider','\WP_Rocket\ServiceProvider\Database' );
class_alias( '\WP_Rocket\Engine\Admin\Database\Subscriber','\WP_Rocket\Subscriber\Admin\Database\Optimization_Subscriber' );

/**
 * Maybe reset opcache after WP Rocket update.
 *
 * @since 3.10.8 deprecated
 * @since  3.1
 * @author Grégory Viguier
 *
 * @param object $wp_upgrader Plugin_Upgrader instance.
 * @param array  $hook_extra  {
 *     Array of bulk item update data.
 *
 *     @type string $action  Type of action. Default 'update'.
 *     @type string $type    Type of update process. Accepts 'plugin', 'theme', 'translation', or 'core'.
 *     @type bool   $bulk    Whether the update process is a bulk update. Default true.
 *     @type array  $plugins Array of the basename paths of the plugins' main files.
 * }
 */
function rocket_maybe_reset_opcache( $wp_upgrader, $hook_extra ) {
	_deprecated_function( __FUNCTION__ . '()', '3.10.8' );
	static $rocket_path;

	if ( ! isset( $hook_extra['action'], $hook_extra['type'], $hook_extra['plugins'] ) ) {
		return;
	}

	if ( 'update' !== $hook_extra['action'] || 'plugin' !== $hook_extra['type'] || ! is_array( $hook_extra['plugins'] ) ) {
		return;
	}

	$plugins = array_flip( $hook_extra['plugins'] );

	if ( ! isset( $rocket_path ) ) {
		$rocket_path = plugin_basename( WP_ROCKET_FILE );
	}

	if ( ! isset( $plugins[ $rocket_path ] ) ) {
		return;
	}

	rocket_reset_opcache();
}

/**
 * Reset PHP opcache.
 *
 * @since 3.10.8 deprecated
 * @since  3.1
 * @author Grégory Viguier
 */
function rocket_reset_opcache() {
	_deprecated_function( __FUNCTION__ . '()', '3.10.8' );
	static $can_reset;

	/**
	 * Triggers before WP Rocket tries to reset OPCache
	 *
	 * @since 3.2.5
	 * @author Remy Perona
	 */
	do_action( 'rocket_before_reset_opcache' );

	if ( ! isset( $can_reset ) ) {
		if ( ! function_exists( 'opcache_reset' ) ) {
			$can_reset = false;

			return false;
		}

		$restrict_api = ini_get( 'opcache.restrict_api' );

		if ( $restrict_api && strpos( __FILE__, $restrict_api ) !== 0 ) {
			$can_reset = false;

			return false;
		}

		$can_reset = true;
	}

	if ( ! $can_reset ) {
		return false;
	}

	$opcache_reset = opcache_reset();

	/**
	 * Triggers after WP Rocket tries to reset OPCache
	 *
	 * @since 3.2.5
	 * @author Remy Perona
	 */
	do_action( 'rocket_after_reset_opcache' );

	return $opcache_reset;
}

/**
 * This notice is displayed after purging OPcache
 *
 * @since 3.10.8 deprecated
 * @since 3.4.1
 * @author Soponar Cristina
 */
function rocket_opcache_purge_result() {
	_deprecated_function( __FUNCTION__ . '()', '3.10.8' );

	if ( ! current_user_can( 'rocket_purge_opcache' ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	$user_id = get_current_user_id();
	$notice  = get_transient( $user_id . '_opcache_purge_result' );
	if ( ! $notice ) {
		return;
	}

	delete_transient( $user_id . '_opcache_purge_result' );

	rocket_notice_html(
		[
			'status'  => $notice['result'],
			'message' => $notice['message'],
		]
	);
}

/**
 * Purge OPCache content in Admin Bar
 *
 * @since 3.10.8 deprecated
 * @since 2.7
 */
function do_admin_post_rocket_purge_opcache() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	_deprecated_function( __FUNCTION__ . '()', '3.10.8' );

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_purge_opcache' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'rocket_purge_opcache' ) ) {
		return;
	}

	$reset_opcache = rocket_reset_opcache();

	if ( ! $reset_opcache ) {
		$op_purge_result = [
			'result'  => 'error',
			'message' => __( 'OPcache purge failed.', 'rocket' ),
		];
	} else {
		$op_purge_result = [
			'result'  => 'success',
			'message' => __( 'OPcache successfully purged', 'rocket' ),
		];
	}

	set_transient( get_current_user_id() . '_opcache_purge_result', $op_purge_result );

	wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	die();
}

/**
 * Do the rollback
 *
 * @since 3.10.10.1 deprecated
 * @since 2.4
 */
function rocket_rollback() {
	_deprecated_function( __FUNCTION__ . '()', '3.11.5' );
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_rollback' ) ) {
		wp_nonce_ays( '' );
	}

	/**
	 * Fires before doing the rollback
	 */
	do_action( 'rocket_before_rollback' );

	$plugin_transient = get_site_transient( 'update_plugins' );
	$plugin_folder    = plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin           = $plugin_folder . '/' . basename( WP_ROCKET_FILE );

	$plugin_transient->response[ $plugin ] = (object) [
		'slug'        => $plugin_folder,
		'new_version' => WP_ROCKET_LASTVERSION,
		'url'         => 'https://wp-rocket.me',
		'package'     => sprintf( 'https://wp-rocket.me/%s/wp-rocket_%s.zip', get_rocket_option( 'consumer_key' ), WP_ROCKET_LASTVERSION ),
	];

	set_site_transient( 'update_plugins', $plugin_transient );

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	// translators: %s is the plugin name.
	$title         = sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
	$nonce         = 'upgrade-plugin_' . $plugin;
	$url           = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
	$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
	$upgrader      = new Plugin_Upgrader( $upgrader_skin );

	remove_filter( 'site_transient_update_plugins', 'rocket_check_update', 1 );
	add_filter( 'update_plugin_complete_actions', 'rocket_rollback_add_return_link' );
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );

	$upgrader->upgrade( $plugin );

	wp_die(
		'',
		// translators: %s is the plugin name.
		esc_html( sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME ) ),
		[
			'response' => 200,
		]
	);
}

/**
 * After a rollback has been done, replace the "return to" link by a link pointing to WP Rocket's tools page.
 * A link to the plugins page is kept in case the plugin is not reactivated correctly.
 *
 * @since 3.10.10.1 deprecated
 * @since  3.2.4
 * @author Grégory Viguier
 * @author Arun Basil Lal
 *
 * @param  array $update_actions Array of plugin action links.
 * @return array                 The array of links where the "return to" link has been replaced.
 */
function rocket_rollback_add_return_link( $update_actions ) {
	_deprecated_function( __FUNCTION__ . '()', '3.11.5' );

	if ( ! isset( $update_actions['plugins_page'] ) ) {
		return $update_actions;
	}

	$update_actions['plugins_page'] = sprintf(
		/* translators: 1 and 3 are link openings, 2 is a link closing. */
		__( '%1$sReturn to WP Rocket%2$s or %3$sgo to Plugins page%2$s', 'rocket' ),
		'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) . '#tools' ) . '" target="_parent">',
		'</a>',
		'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" target="_parent">'
	);

	return $update_actions;
}
