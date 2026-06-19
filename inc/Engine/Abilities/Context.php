<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

class Context {
	/**
	 * Determines whether WP Rocket abilities are enabled for MCP.
	 *
	 * @return bool True when WP Rocket abilities should be registered; false otherwise.
	 */
	public function is_enabled(): bool {
		/**
		 * Filters whether WP Rocket abilities are enabled for MCP.
		 *
		 * @param bool $enabled Whether abilities are enabled. Default false.
		 * @return bool
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_enable_abilities', false );
	}
}
