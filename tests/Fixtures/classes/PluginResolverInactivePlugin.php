<?php

namespace WP_Rocket\Tests\Fixtures\classes;

use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Stub implementing PluginCompatibilityInterface, reporting itself inactive.
 * Used to test PluginResolver's exclusion branch.
 */
class PluginResolverInactivePlugin implements PluginCompatibilityInterface {
	/**
	 * Always reports itself as inactive.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return false;
	}
}
