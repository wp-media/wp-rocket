<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\PageBuilder;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility with Thrive Visual Editor.
 */
class ThriveVisualEditor implements Subscriber_Interface, PluginCompatibilityInterface {
	use ReturnTypesTrait;

	/**
	 * Whether Thrive Visual Editor is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return function_exists( 'tve_editor_url' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			// Forces Thrive Visual Editor's bot detection to assume a human visitor.
			'tve_dash_is_crawler' => [ 'return_zero', PHP_INT_MAX ],
		];
	}
}
