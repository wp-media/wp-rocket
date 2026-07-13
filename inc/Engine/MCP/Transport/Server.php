<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP\MCP\Core\McpAdapter;
use WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler;

class Server {
	/**
	 * Register the MCP server with the adapter.
	 *
	 * Creates an isolated server at /wp-json/mcp/mcp-oauth-server using the custom
	 * OAuthHttpTransport for JWT Bearer authentication.
	 *
	 * @return void
	 */
	public function register_server(): void {
		if ( ! class_exists( McpAdapter::class ) ) {
			return;
		}

		$adapter = McpAdapter::instance();

		$adapter->create_server(
			'mcp-oauth-server',
			'mcp',
			'mcp-oauth-server',
			'MCP OAuth Server',
			'MCP Server with OAuth 2.1 authentication',
			'v1.0.0',
			[ OAuthHttpTransport::class ],
			ErrorLogMcpErrorHandler::class,
			McpObservabilityHandler::class,
			[
				'mcp-adapter/discover-abilities',
				'mcp-adapter/get-ability-info',
				'mcp-adapter/execute-ability',
			]
		);
	}
}
