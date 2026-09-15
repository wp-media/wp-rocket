<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility with Visual Composer / WPBakery Page Builder.
 */
class VisualComposer implements Subscriber_Interface, PluginCompatibilityInterface {
	use ReturnTypesTrait;

	/**
	 * Whether Visual Composer is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return rocket_has_constant( 'WPB_VC_VERSION' ) && class_exists( 'Vc_Manager' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			// Disable nonce checking for the Visual Composer grid.
			'vc_grid_get_grid_data_access' => 'return_true',
		];
	}
}
