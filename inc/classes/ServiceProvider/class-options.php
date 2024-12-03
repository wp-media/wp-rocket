<?php
declare(strict_types=1);

namespace WP_Rocket\ServiceProvider;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Admin\Options as OptionsAPI;
use WP_Rocket\Admin\Options_Data;

/**
 * Service provider for the WP Rocket options
 */
class Options extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		Options_Data::class,
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
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( Options_Data::class )
			->addArgument( $this->getContainer()->get( OptionsAPI::class )->get( 'settings', [] ) );
	}
}
