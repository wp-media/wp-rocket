<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Beacon;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Support\Data;

/**
 * Service Provider for Beacon
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'beacon',
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
		$this->getContainer()->addShared( 'beacon', Beacon::class )
			->addArguments(
				[
					Options_Data::class,
					$this->getContainer()->get( 'template_path' ) . '/settings',
					Data::class
				]
			);
	}
}
