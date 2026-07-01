<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP\MCP\Abilities\DiscoverAbilitiesAbility;
use WP\MCP\Abilities\ExecuteAbilityAbility;
use WP\MCP\Abilities\GetAbilityInfoAbility;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * MCP server instance.
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * Constructor.
	 *
	 * @param Server $server MCP server instance.
	 */
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
			'wp_abilities_api_init' => [ 'ensure_shared_abilities_registered', 20 ],
			'mcp_adapter_init'      => 'register_server',
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

	/**
	 * Ensure the mcp-adapter package's shared abilities exist before our
	 * server references them as tools.
	 *
	 * Our server's tool list reuses the mcp-adapter package's own
	 * discover-abilities/get-ability-info/execute-ability abilities rather
	 * than duplicating them. Those are normally registered by the package's
	 * *default* server (McpAdapter::maybe_create_default_server()), which is
	 * gated behind the `mcp_adapter_create_default_server` filter — disabled
	 * in our own test bootstrap, and something any site could legitimately
	 * disable. Registering our own server must not depend on that unrelated
	 * toggle, so we register these abilities ourselves if nobody else has.
	 * Runs at priority 20 so a genuinely-enabled default server (the common
	 * case) registers them first and this becomes a no-op.
	 *
	 * @return void
	 */
	public function ensure_shared_abilities_registered(): void {
		if ( wp_get_ability( 'mcp-adapter/discover-abilities' ) ) {
			return;
		}

		if ( function_exists( 'wp_register_ability_category' ) ) {
			wp_register_ability_category(
				'mcp-adapter',
				[
					'label'       => 'MCP Adapter',
					'description' => 'Abilities for the MCP Adapter',
				]
			);
		}

		DiscoverAbilitiesAbility::register();
		GetAbilityInfoAbility::register();
		ExecuteAbilityAbility::register();
	}
}
