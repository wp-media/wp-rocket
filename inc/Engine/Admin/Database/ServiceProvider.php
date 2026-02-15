<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Database;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for database optimization
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'db_optimization_process',
		'db_optimization',
		'db_optimization_subscriber',
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
		$this->getContainer()->add( 'db_optimization_process', OptimizationProcess::class );
		$this->getContainer()->add( 'db_optimization', Optimization::class )
			->addArgument( 'db_optimization_process' );
		$this->getContainer()->addShared( 'db_optimization_subscriber', Subscriber::class )
			->addArguments(
				[
					'db_optimization',
					'options',
				]
			);
	}
}
