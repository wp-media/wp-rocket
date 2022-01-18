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
