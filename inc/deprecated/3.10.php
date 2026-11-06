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
