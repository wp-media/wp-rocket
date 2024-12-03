<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Varnish;

use WP_Rocket\Admin\Options_Data;
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
		Varnish::class,
		Subscriber::class,
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
		$this->getContainer()->add( Varnish::class );
		$this->getContainer()->addShared( Subscriber::class )
			->addArguments(
				[
					Varnish::class,
					Options_Data::class,
				]
			);
	}
}
