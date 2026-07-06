<?php
/**
 * MCP Auth Service Provider.
 *
 * Registers the OAuth 2.1 authentication components with the DI container.
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Auth;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints as DiscoveryEndpoints;
use WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber as DiscoverySubscriber;
use WP_Rocket\Engine\MCP\Context;

/**
 * Service provider for the MCP Auth module.
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * Services provided by this service provider.
	 *
	 * @var string[]
	 */
	protected $provides = [
		'mcp_auth_claude_verifier',
		'mcp_auth_cimd_resolver',
		'mcp_auth_authorize_endpoint',
		'mcp_auth_authorize_callback',
		'mcp_auth_token_endpoint',
		'mcp_auth_consent_endpoint',
		'mcp_auth_revoke_endpoint',
		'mcp_auth_discovery_endpoints',
		'mcp_auth_discovery_subscriber',
		'mcp_auth_rewrite',
		'mcp_auth_subscriber',
		'mcp_context',
	];

	/**
	 * Check if this service provider provides a given service.
	 *
	 * @param string $id Service ID.
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Register services in the container.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'mcp_context', Context::class );

		$this->getContainer()->addShared( 'mcp_auth_discovery_endpoints', DiscoveryEndpoints::class );
		$this->getContainer()->addShared( 'mcp_auth_discovery_subscriber', DiscoverySubscriber::class )
			->addArguments( [ 'mcp_auth_discovery_endpoints', 'mcp_context' ] );

		$this->getContainer()->addShared( 'mcp_auth_claude_verifier', ClaudeClientVerifier::class );
		$this->getContainer()->addShared( 'mcp_auth_cimd_resolver', CimdResolver::class )
			->addArgument( 'mcp_auth_claude_verifier' );
		$this->getContainer()->addShared( 'mcp_auth_authorize_endpoint', AuthorizeEndpoint::class )
			->addArgument( 'mcp_auth_cimd_resolver' );
		$this->getContainer()->addShared( 'mcp_auth_authorize_callback', AuthorizeCallback::class );
		$this->getContainer()->addShared( 'mcp_auth_token_endpoint', TokenEndpoint::class );
		$this->getContainer()->addShared( 'mcp_auth_consent_endpoint', ConsentEndpoint::class );
		$this->getContainer()->addShared( 'mcp_auth_revoke_endpoint', RevokeEndpoint::class );
		$this->getContainer()->addShared( 'mcp_auth_rewrite', Rewrite::class );
		$this->getContainer()->addShared( 'mcp_auth_subscriber', Subscriber::class )
			->addArguments(
				[
					'mcp_auth_rewrite',
					'mcp_auth_authorize_endpoint',
					'mcp_auth_authorize_callback',
					'mcp_auth_token_endpoint',
					'mcp_auth_consent_endpoint',
					'mcp_auth_revoke_endpoint',
					'mcp_context',
				]
				);
	}
}
