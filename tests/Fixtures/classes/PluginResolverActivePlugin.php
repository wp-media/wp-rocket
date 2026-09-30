<?php

namespace WP_Rocket\Tests\Fixtures\classes;

use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Stub implementing PluginCompatibilityInterface, reporting itself active.
 * Used to test PluginResolver's inclusion branch.
 */
class PluginResolverActivePlugin implements PluginCompatibilityInterface {
	/**
	 * Always reports itself as active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return true;
	}
}
