<?php

namespace WP_Rocket\Logger;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'logger',
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
	 * Register classes provided.
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'logger', Logger::class );
		$this->getContainer()
			->inflector( LoggerAwareInterface::class )
			->invokeMethod( 'set_logger', [ $this->getContainer()->get( 'logger' ) ] );
	}
}
