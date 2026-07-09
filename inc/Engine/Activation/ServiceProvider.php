<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Activation;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Engine\Capabilities\Manager;
use WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints as McpAuthDiscoveryEndpoints;
use WP_Rocket\Engine\MCP\Auth\Rewrite as McpAuthRewrite;
use WP_Rocket\Engine\MCP\Auth\SecretManager as McpAuthSecretManager;

/**
 * Service Provider for the activation process.
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'advanced_cache',
		'capabilities_manager',
		'wp_cache',
		'action_scheduler_check',
		'mcp_auth_discovery_endpoints',
		'mcp_auth_rewrite',
		'mcp_secret_manager',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Executes this method when the service provider is registered
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->getContainer()
			->inflector( ActivationInterface::class )
			->invokeMethod( 'activate', [] );
	}

	/**
	 * Registers the option array in the container.
	 */
	public function register(): void {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'advanced_cache', AdvancedCache::class )
			->addArguments(
				[
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' ),
					$filesystem,
				]
			);
		$this->getContainer()->add( 'capabilities_manager', Manager::class );
		$this->getContainer()->add( 'wp_cache', WPCache::class )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'action_scheduler_check', ActionSchedulerCheck::class );
		$this->getContainer()->add( 'mcp_auth_discovery_endpoints', McpAuthDiscoveryEndpoints::class );
		$this->getContainer()->add( 'mcp_auth_rewrite', McpAuthRewrite::class );
		$this->getContainer()->add( 'mcp_secret_manager', McpAuthSecretManager::class );
	}
}
