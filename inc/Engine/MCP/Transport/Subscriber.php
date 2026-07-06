<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP\MCP\Abilities\DiscoverAbilitiesAbility;
use WP\MCP\Abilities\ExecuteAbilityAbility;
use WP\MCP\Abilities\GetAbilityInfoAbility;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * MCP server instance.
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * OAuth server context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Server  $server  MCP server instance.
	 * @param Context $context OAuth server context.
	 */
	public function __construct( Server $server, Context $context ) {
		$this->server  = $server;
		$this->context = $context;
	}

	/**
	 * Get the subscribed events.
	 *
	 * @return array<string, string|array>
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_categories_init' => 'ensure_default_category',
			'wp_abilities_api_init'            => 'ensure_shared_abilities_registered',
			'mcp_adapter_init'                 => 'register_server',
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
		if ( ! $this->context->is_enabled() ) {
			return;
		}

		$this->server->register_server();
	}

	/**
	 * Whether nobody else will register the shared mcp-adapter abilities.
	 *
	 * True only when the mcp-adapter package's *default* server is disabled
	 * via the `mcp_adapter_create_default_server` filter — the only thing
	 * that normally registers the 'mcp-adapter' category and its three
	 * shared abilities (McpAdapter::maybe_create_default_server()).
	 *
	 * @return bool
	 */
	private function must_register_shared_abilities_ourselves(): bool {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- reading the mcp-adapter package's own filter, not defining a new hook.
		return ! wpm_apply_filters_typed( 'boolean', 'mcp_adapter_create_default_server', true );
	}

	/**
	 * Register the 'mcp-adapter' ability category ourselves when the
	 * default server (which normally owns this) is disabled.
	 *
	 * Must run on the `wp_abilities_api_categories_init` action specifically
	 * — the Abilities API rejects category registration anywhere else.
	 *
	 * @return void
	 */
	public function ensure_default_category(): void {
		if ( ! $this->must_register_shared_abilities_ourselves() ) {
			return;
		}

		wp_register_ability_category(
			'mcp-adapter',
			[
				'label'       => 'MCP Adapter',
				'description' => 'Abilities for the MCP Adapter',
			]
		);
	}

	/**
	 * Register the mcp-adapter package's shared discover-abilities/
	 * get-ability-info/execute-ability abilities ourselves when the default
	 * server (which normally owns them) is disabled.
	 *
	 * Our server's tool list reuses these three abilities rather than
	 * duplicating them, but they are normally only registered by the
	 * mcp-adapter package's *default* server — gated behind the
	 * `mcp_adapter_create_default_server` filter, disabled in our own test
	 * bootstrap and something any site could legitimately disable.
	 * Registering our own server must not depend on that unrelated toggle.
	 *
	 * @return void
	 */
	public function ensure_shared_abilities_registered(): void {
		if ( ! $this->must_register_shared_abilities_ourselves() ) {
			return;
		}

		DiscoverAbilitiesAbility::register();
		GetAbilityInfoAbility::register();
		ExecuteAbilityAbility::register();
	}
}
