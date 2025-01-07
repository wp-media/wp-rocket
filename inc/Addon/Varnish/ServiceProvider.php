<?php
namespace WP_Rocket\Addon\Varnish;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for Varnish Addon.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'varnish',
		'varnish_subscriber',
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
		$this->getContainer()->add( 'varnish', Varnish::class );
		$this->getContainer()->addShared( 'varnish_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'varnish' ) )
			->addArgument( $this->getContainer()->get( 'options' ) );
	}
}
