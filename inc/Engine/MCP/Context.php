<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\MCP;

class Context {
	/**
	 * Determines whether the MCP OAuth server is enabled.
	 *
	 * @return bool True when the OAuth server (rewrite rules, endpoints,
	 *              discovery documents, transport) should be registered; false otherwise.
	 */
	public function is_enabled(): bool {
		/**
		 * Filters whether the MCP OAuth server is enabled.
		 *
		 * When `false`, WP Rocket does not register the OAuth rewrite rules or
		 * respond to any /oauth/* endpoint or /.well-known discovery request,
		 * and does not register the MCP OAuth transport server.
		 *
		 * @param bool $enabled Whether the MCP OAuth server is enabled. Default false.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_mcp_oauth_server_enabled', false );
	}
}
