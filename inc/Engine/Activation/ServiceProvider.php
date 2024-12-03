<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Engine\Capabilities\Manager;
use WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck;

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
		AdvancedCache::class,
		Manager::class,
		WPCache::class,
		ActionSchedulerCheck::class,
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

		$this->getContainer()->add( AdvancedCache::class )
			->addArguments(
				[
					$this->getContainer()->get( 'template_path' ) . '/cache/',
					$filesystem,
				]
			);
		$this->getContainer()->add( Manager::class );
		$this->getContainer()->add( WPCache::class )
			->addArgument( $filesystem );
		$this->getContainer()->add( ActionSchedulerCheck::class );
	}
}
