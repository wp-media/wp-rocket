<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Debug;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\{AbstractServiceProvider, BootableServiceProviderInterface};

/**
 * Service provider for Debug
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'debug_subscriber',
	];

	/**
	 * Array of available debug services.
	 *
	 * @var array
	 */
	protected $services = [];

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
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->services = $this->getContainer()->get( 'debug_resolver' )->get_services();

		if ( empty( $this->services ) ) {
			return;
		}

		$this->provides[] = 'options_debug';

		foreach ( $this->services as $service ) {
			$this->provides[] = $service['service'];
		}
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->addShared( 'debug_subscriber', DebugSubscriber::class );

		if ( empty( $this->services ) ) {
			return;
		}

		$this->container->add( 'options_debug', Options_Data::class )
			->addArgument( $this->container->get( 'options_api' )->get( 'debug', [] ) );

		foreach ( $this->services as $service ) {
			$this->getContainer()->add( $service['service'], $service['class'] )
				->addArgument( 'options_debug' )
				->addArgument( 'options_api' );
		}
	}
}
