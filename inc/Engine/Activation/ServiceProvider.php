<?php
namespace WP_Rocket\Engine\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Engine\Capabilities\Manager;
use WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck;

/**
 * Service Provider for the activation process.
 *
 * @since 3.6.3
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'advanced_cache',
		'capabilities_manager',
		'wp_cache',
		'action_scheduler_check',
	];

	/**
	 * Executes this method when the service provider is registered
	 *
	 * @return void
	 */
	public function boot() {
		$this->getContainer()
			->inflector( ActivationInterface::class )
			->invokeMethod( 'activate', [] );
	}

	/**
	 * Registers the option array in the container.
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'advanced_cache', AdvancedCache::class )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'capabilities_manager', Manager::class );
		$this->getContainer()->add( 'wp_cache', WPCache::class )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'action_scheduler_check', ActionSchedulerCheck::class );
	}
}
