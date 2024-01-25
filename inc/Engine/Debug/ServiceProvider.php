<?php
namespace WP_Rocket\Engine\Debug;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\Engine\Debug\Resolver;

/**
 * Service provider for Debug
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
	protected $provides = [];

	/**
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot() {
		$services = Resolver::get_services();

		if ( empty( $services ) ) {
			return;
		}

		foreach ( $services as $service ) {
			$this->provides[] = $service['service'];
		}
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options  = $this->getContainer()->get( 'options_debug' );
		$services = Resolver::get_services();

		if ( empty( $services ) ) {
			return;
		}

		foreach ( $services as $service ) {
			$this->getContainer()->add( $service['service'], $service['class'] )
				->addArgument( $options )
				->addArgument( $this->getContainer()->get( 'options_api' ) );
		}
	}
}
