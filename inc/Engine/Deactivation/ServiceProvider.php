<?php
namespace WP_Rocket\Engine\Deactivation;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

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
	];

	/**
	 * Executes this method when the service provider is registered
	 *
	 * @return void
	 */
	public function boot() {
		$this->getContainer()
			->inflector( 'WP_Rocket\Engine\Deactivation\DeactivationInterface' )
			->invokeMethod( 'deactivate', [] );
	}

	/**
	 * Registers the option array in the container.
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'advanced_cache', 'WP_Rocket\Engine\Cache\AdvancedCache' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->withArgument( $filesystem );
		$this->getContainer()->add( 'capabilities_manager', 'WP_Rocket\Engine\Capabilities\Manager' );
		$this->getContainer()->add( 'wp_cache', 'WP_Rocket\Engine\Cache\WPCache' )
			->withArgument( $filesystem );
	}
}
