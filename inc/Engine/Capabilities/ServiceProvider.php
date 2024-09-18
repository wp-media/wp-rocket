<?php
namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for capabilities
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'capabilities_manager',
		'capabilities_subscriber',
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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'capabilities_manager', Manager::class );
		$this->getContainer()->addShared( 'capabilities_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'capabilities_manager' ) );
	}
}
