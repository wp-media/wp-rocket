<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Class that handles events related to Next Gen Gallery.
 *
 * @since  3.3.1
 */
class NGG implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return class_exists( 'C_NextGEN_Bootstrap' );
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'run_ngg_resource_manager' => 'deactivate_resource_manager',
		];
	}

	/**
	 * Deactivate NGG Resource Manager to prevent conflict with WP Rocket output buffering
	 *
	 * @since 3.3.1
	 *
	 * @param bool $valid_request Indicates if the current request is valid for the NGG resource manager.
	 *
	 * @return bool
	 */
	public function deactivate_resource_manager( $valid_request ) {
		if ( is_admin() ) {
			return $valid_request;
		}

		return false;
	}
}
