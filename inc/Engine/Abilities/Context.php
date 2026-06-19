<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

class Context {
	/**
	 * Determines whether WP Rocket abilities are enabled for MCP.
	 *
	 * By default this returns false, meaning NO wp-rocket/* abilities or ability categories
	 * are registered with the WordPress Abilities API. This is an intentional opt-in: site owners
	 * must explicitly enable AI-agent access to WP Rocket by filtering this value to true.
	 *
	 * Effect when false: wp_get_abilities() will not include any wp-rocket/* ability, and the
	 * wp-rocket-options and wp-rocket-insights ability categories will not be registered.
	 *
	 * @since 3.23
	 *
	 * @return bool True when WP Rocket abilities should be registered; false otherwise.
	 */
	public function is_enabled(): bool {
		/**
		 * Filters whether WP Rocket abilities are enabled for MCP.
		 *
		 * When this filter returns false (the default), no wp-rocket/* abilities or ability
		 * categories are registered — an explicit opt-in is required to expose WP Rocket
		 * controls to AI agents via the WordPress Abilities API.
		 *
		 * Example (mu-plugin or theme functions.php):
		 *   add_filter( 'rocket_enable_abilities', '__return_true' );
		 *
		 * @since 3.23
		 *
		 * @param bool $enabled Whether abilities are enabled. Default false.
		 * @return bool
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_enable_abilities', false );
	}
}
