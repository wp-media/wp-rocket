<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class that handles events related to Next Gen Gallery.
 *
 * @since  3.3.1
 * @author Remy Perona
 */
class NGG_Subscriber implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) ) {
			return;
		}

		return [
			'run_ngg_resource_manager' => 'deactivate_resource_manager',
		];
	}

	/**
	 * Deactivate NGG Resource Manager to prevent conflict with WP Rocket output buffering
	 *
	 * @since 3.3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function deactivate_resource_manager() {
		return false;
	}
}
