<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty;

/**
 * Marker interface for third-party plugin compatibility classes that can
 * report their own activation state.
 */
interface PluginCompatibilityInterface {
	/**
	 * Whether the target third-party plugin is active.
	 *
	 * Static: detection is plugin-presence only and requires no construction.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool;
}
