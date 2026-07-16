<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Compat;

/**
 * Deprecated MCP OAuth filter management.
 *
 * The MCP OAuth flow now lives entirely in the wp-media/mcp-oauth library, which
 * renamed WP Rocket's two public filters. The library still reads the legacy
 * `rocket_*` names internally (its own back-compat bridge), so their *values*
 * keep flowing — this shim only emits a deprecation notice when a third party
 * has actually registered a callback on a legacy filter. It never touches values.
 *
 * This is the only custom WP Rocket code that survives the migration; everything
 * else is delegated to the library.
 */
class DeprecatedFilters {
	/**
	 * Register the deprecation notice on the 'init' hook.
	 *
	 * Called from inc/main.php at plugin load, before 'init' fires.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', [ new self(), 'maybe_notify_deprecated_filters' ], 1 );
	}

	/**
	 * Emit a deprecation notice for each legacy filter that has a callback.
	 *
	 * Guarded by has_filter() so the notice only fires when a third party is
	 * actually using a legacy filter. Values are still honored by the library's
	 * own back-compat bridge, so nothing is bridged here.
	 *
	 * @return void
	 */
	public function maybe_notify_deprecated_filters(): void {
		if ( has_filter( 'rocket_mcp_oauth_server_enabled' ) ) {
			_deprecated_hook( 'rocket_mcp_oauth_server_enabled', '3.24', 'wpmedia_mcp_oauth_server_enabled' );
		}

		if ( has_filter( 'rocket_mcp_trusted_publishers' ) ) {
			_deprecated_hook( 'rocket_mcp_trusted_publishers', '3.24', 'wpmedia_mcp_oauth_trusted_publishers' );
		}
	}
}
