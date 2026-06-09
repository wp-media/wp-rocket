<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * MCP server instance.
	 *
	 * @var Server
	 */
	private $server;

	public function __construct( Server $server ) {
		$this->server = $server;
	}

	/**
	 * Get the subscribed events.
	 *
	 * @return array<string, string|array>
	 */
	public static function get_subscribed_events(): array {
		return [
			'mcp_adapter_init' => 'register_server',
		];
	}

	/**
	 * Register the MCP server with the adapter.
	 *
	 * Creates an isolated server at /wp-json/mcp/mcp-oauth-server using the custom
	 * OAuthHttpTransport for JWT Bearer authentication.
	 *
	 * @return void
	 */
	public function register_server(): void {
		$this->server->register_server();
	}
}
