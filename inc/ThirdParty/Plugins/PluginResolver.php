<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Resolves the set of active third-party plugin compatibility subscriber ids.
 *
 * Phase 0 scaffolding for issue #6418: any class not yet implementing
 * PluginCompatibilityInterface defaults to active, so the returned set equals
 * today's full static set. Real detection is opted in per class in Phase 1.
 */
class PluginResolver {
	/**
	 * Memoized active plugin ids.
	 *
	 * @var array<string>|null
	 */
	private static $active_plugins = null;

	/**
	 * Ids of plugin-compat subscribers whose target plugin is active.
	 *
	 * @param bool $force Bypass memoization (testing).
	 *
	 * @return array<string>
	 */
	public static function get_active_plugins( bool $force = false ): array {
		if ( ! $force && null !== self::$active_plugins ) {
			return self::$active_plugins;
		}

		self::$active_plugins = self::filter_active_registry( ( new SubscriberFactory() )->get_registry() );

		return self::$active_plugins;
	}

	/**
	 * Filters an id => class registry down to the ids whose class reports itself active.
	 *
	 * @param array<string,string> $registry Id => FQCN map.
	 *
	 * @return array<string>
	 */
	private static function filter_active_registry( array $registry ): array {
		$active = [];

		foreach ( $registry as $id => $class ) {
			if ( ! is_a( $class, PluginCompatibilityInterface::class, true ) ) {
				// Default-active until the class opts in to PluginCompatibilityInterface (Phase 1).
				$active[] = $id;
				continue;
			}

			if ( $class::is_activated() ) {
				$active[] = $id;
			}
		}

		return $active;
	}
}
