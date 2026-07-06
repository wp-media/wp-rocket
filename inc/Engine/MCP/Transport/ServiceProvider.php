<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the MCP custom transport module.
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * Services provided by this service provider.
	 *
	 * @var string[]
	 */
	protected $provides = [
		'mcp_transport_server',
		'mcp_transport_subscriber',
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
		$this->getContainer()->add( 'mcp_transport_server', Server::class );
		$this->getContainer()->addShared( 'mcp_transport_subscriber', Subscriber::class )
			->addArguments( [ 'mcp_transport_server', 'mcp_context' ] );
	}
}
